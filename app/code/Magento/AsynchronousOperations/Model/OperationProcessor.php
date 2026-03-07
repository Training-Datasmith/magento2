<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;
use Magento\Framework\Bulk\OperationManagementInterface;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Psr\Log\LoggerInterface;

/**
 * Proccess operation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OperationProcessor
{
    /**
     * OperationProcessor constructor.
     */
    public function __construct(private readonly MessageValidator $messageValidator, private readonly MessageEncoder $messageEncoder, private readonly ConsumerConfigurationInterface $configuration, private readonly Json $jsonHelper, private readonly OperationManagementInterface $operationManagement, private readonly ServiceOutputProcessor $serviceOutputProcessor, private readonly CommunicationConfig $communicationConfig, private readonly LoggerInterface $logger)
    {
    }

    /**
     * Process topic-based encoded message
     */
    public function process(string $encodedMessage): void
    {
        $operation = $this->messageEncoder->decode(AsyncConfig::SYSTEM_TOPIC_NAME, $encodedMessage);
        $this->messageValidator->validate(AsyncConfig::SYSTEM_TOPIC_NAME, $operation);

        $status = OperationInterface::STATUS_TYPE_COMPLETE;
        $errorCode = null;
        $messages = [];
        $entityParams = [];
        $topicName = $operation->getTopicName();
        $handlers = $this->configuration->getHandlers($topicName);
        try {
            $data = $this->jsonHelper->unserialize($operation->getSerializedData());
            $entityParams = $this->messageEncoder->decode($topicName, $data['meta_information']);
            $this->messageValidator->validate($topicName, $entityParams);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $messages[] = [$e->getMessage()];
        }

        $outputData = null;
        if ($errorCode === null) {
            foreach ($handlers as $callback) {
                $result = $this->executeHandler($callback, $entityParams);
                $status = $result['status'];
                $errorCode = $result['error_code'];
                $messages[] = $result['messages'];
                $outputData = $result['output_data'];
            }
        }

        if (isset($outputData)) {
            try {
                $communicationConfig = $this->communicationConfig->getTopic($topicName);
                $asyncHandler =
                    $communicationConfig[CommunicationConfig::TOPIC_HANDLERS][AsyncConfig::DEFAULT_HANDLER_NAME];
                $serviceClass = $asyncHandler[CommunicationConfig::HANDLER_TYPE];
                $serviceMethod = $asyncHandler[CommunicationConfig::HANDLER_METHOD];
                $outputData = $this->serviceOutputProcessor->process(
                    $outputData,
                    $serviceClass,
                    $serviceMethod
                );
                $outputData = $this->jsonHelper->serialize($outputData);
            } catch (\Exception $e) {
                $messages[] = [$e->getMessage()];
            }
        }

        $serializedData = (isset($errorCode)) ? $operation->getSerializedData() : null;
        $this->operationManagement->changeOperationStatus(
            $operation->getBulkUuid(),
            $operation->getId(),
            $status,
            $errorCode,
            implode('; ', array_merge([], ...$messages)),
            $serializedData,
            $outputData
        );
    }

    /**
     * Execute topic handler
     *
     * @param callable $callback
     * @param array $entityParams
     */
    private function executeHandler($callback, $entityParams): array
    {
        $result = [
            'status' => OperationInterface::STATUS_TYPE_COMPLETE,
            'error_code' => null,
            'messages' => [],
            'output_data' => null,
        ];
        try {
            // phpcs:disable Magento2.Functions.DiscouragedFunction
            $result['output_data'] = call_user_func_array($callback, $entityParams);
            // phpcs:enable Magento2.Functions.DiscouragedFunction
            $result['messages'][] = sprintf('Service execution success %s::%s', $callback[0]::class, $callback[1]);
        } catch (\Zend_Db_Adapter_Exception  $e) {
            $this->logger->critical($e->getMessage());
            if ($e instanceof LockWaitException
                || $e instanceof DeadlockException
                || $e instanceof ConnectionException
            ) {
                $result['status'] = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
                $result['error_code'] = $e->getCode();
                $result['messages'][] = __($e->getMessage());
            } else {
                $result['status'] = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                $result['error_code'] = $e->getCode();
                $result['messages'][] =
                    __('Sorry, something went wrong during product prices update. Please see log for details.');
            }
        } catch (NoSuchEntityException|LocalizedException|\Exception $e) {
            $this->logger->error($e->getMessage());
            $result['status'] = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $result['error_code'] = $e->getCode();
            $result['messages'][] = $e->getMessage();
        }
        return $result;
    }
}
