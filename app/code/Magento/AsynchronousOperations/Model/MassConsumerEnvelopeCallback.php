<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageLockException;
use Magento\Framework\MessageQueue\QueueInterface;
use Psr\Log\LoggerInterface;

/**
 * Class used as public callback function by async consumer.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassConsumerEnvelopeCallback
{
    /**
     * @var OperationProcessor
     */
    private $operationProcessor;

    /**
     * @var MessageControllerDecorator
     */
    private $messageControllerDecorator;

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly ConsumerConfigurationInterface $configuration,
        OperationProcessorFactory $operationProcessorFactory,
        private readonly LoggerInterface $logger,
        private readonly QueueInterface $queue,
        ?MessageControllerDecorator $messageControllerDecorator = null
    ) {
        $this->operationProcessor = $operationProcessorFactory->create(
            [
                'configuration' => $this->configuration,
            ]
        );
        $this->messageControllerDecorator = $messageControllerDecorator
            ?: ObjectManager::getInstance()->get(MessageControllerDecorator::class);
    }

    /**
     * Get transaction callback. This handles the case of async.
     */
    public function execute(EnvelopeInterface $message): void
    {
        $queue = $this->queue;
        /** @var LockInterface $lock */
        $lock = null;
        try {
            $topicName = $message->getProperties()['topic_name'];
            $lock = $this->messageControllerDecorator->lock($message, $this->configuration->getConsumerName());

            $allowedTopics = $this->configuration->getTopicNames();
            if (in_array($topicName, $allowedTopics)) {
                $this->operationProcessor->process($message->getBody());
            } else {
                $queue->reject($message);
                return;
            }
            $queue->acknowledge($message);
        } catch (MessageLockException) {
            $queue->acknowledge($message);
        } catch (ConnectionLostException) {
            if ($lock) {
                $this->resource->getConnection()
                    ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
            }
        } catch (NotFoundException $e) {
            $queue->acknowledge($message);
            $this->logger->warning($e->getMessage());
        } catch (\Exception $e) {
            $queue->reject($message, false, $e->getMessage());
            if ($lock) {
                $this->resource->getConnection()
                    ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
            }
        }
    }

    /**
     * Get message queue.
     *
     * @return QueueInterface
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
