<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
use Magento\Asynchronous_Operations\Model\Config_Interface as AsyncConfig;
use Magento\Framework\Bulk\Operation_Management_Interface;
use Magento\Framework\Communication\Config_Interface as CommunicationConfig;
use Magento\Framework\DB\Adapter\Connection_Exception;
use Magento\Framework\DB\Adapter\Deadlock_Exception;
use Magento\Framework\DB\Adapter\Lock_Wait_Exception;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Framework\Message_Queue\Consumer_Configuration_Interface;
use Magento\Framework\Message_Queue\Message_Encoder;
use Magento\Framework\Message_Queue\Message_Validator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Service_Output_Processor;
use Psr\Log\Logger_Interface;
/**
 * Proccess operation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Operation_Processor
{
    /**
     * OperationProcessor constructor.
     */
    public function __construct(private readonly Message_Validator $message_validator, private readonly Message_Encoder $message_encoder, private readonly Consumer_Configuration_Interface $configuration, private readonly Json $json_helper, private readonly Operation_Management_Interface $operation_management, private readonly Service_Output_Processor $service_output_processor, private readonly Communication_Config $communication_config, private readonly Logger_Interface $logger)
    {
    }
    /**
     * Process topic-based encoded message
     */
    public function process(string $encoded_message): void
    {
        $operation = $this->message_encoder->decode(Async_Config::SYSTEM_TOPIC_NAME, $encoded_message);
        $this->message_validator->validate(Async_Config::SYSTEM_TOPIC_NAME, $operation);
        $status = Operation_Interface::STATUS_TYPE_COMPLETE;
        $error_code = null;
        $messages = [];
        $entity_params = [];
        $topic_name = $operation->get_topic_name();
        $handlers = $this->configuration->get_handlers($topic_name);
        try {
            $data = $this->json_helper->unserialize($operation->get_serialized_data());
            $entity_params = $this->message_encoder->decode($topic_name, $data['meta_information']);
            $this->message_validator->validate($topic_name, $entity_params);
        } catch (\Exception $e) {
            $this->logger->error($e->get_message());
            $status = Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $error_code = $e->get_code();
            $messages[] = [$e->get_message()];
        }
        $output_data = null;
        if ($error_code === null) {
            foreach ($handlers as $callback) {
                $result = $this->execute_handler($callback, $entity_params);
                $status = $result['status'];
                $error_code = $result['error_code'];
                $messages[] = $result['messages'];
                $output_data = $result['output_data'];
            }
        }
        if (isset($output_data)) {
            try {
                $communication_config = $this->communication_config->get_topic($topic_name);
                $async_handler = $communication_config[Communication_Config::TOPIC_HANDLERS][Async_Config::DEFAULT_HANDLER_NAME];
                $service_class = $async_handler[Communication_Config::HANDLER_TYPE];
                $service_method = $async_handler[Communication_Config::HANDLER_METHOD];
                $output_data = $this->service_output_processor->process($output_data, $service_class, $service_method);
                $output_data = $this->json_helper->serialize($output_data);
            } catch (\Exception $e) {
                $messages[] = [$e->get_message()];
            }
        }
        $serialized_data = isset($error_code) ? $operation->get_serialized_data() : null;
        $this->operation_management->change_operation_status($operation->get_bulk_uuid(), $operation->get_id(), $status, $error_code, implode('; ', array_merge([], ...$messages)), $serialized_data, $output_data);
    }
    /**
     * Execute topic handler
     *
     * @param callable $callback
     * @param array $entityParams
     */
    private function execute_handler($callback, $entity_params): array
    {
        $result = ['status' => Operation_Interface::STATUS_TYPE_COMPLETE, 'error_code' => null, 'messages' => [], 'output_data' => null];
        try {
            // phpcs:disable Magento2.Functions.DiscouragedFunction
            $result['output_data'] = call_user_func_array($callback, $entity_params);
            // phpcs:enable Magento2.Functions.DiscouragedFunction
            $result['messages'][] = sprintf('Service execution success %s::%s', $callback[0]::class, $callback[1]);
        } catch (\Zend_Db_Adapter_Exception $e) {
            $this->logger->critical($e->get_message());
            if ($e instanceof Lock_Wait_Exception || $e instanceof Deadlock_Exception || $e instanceof Connection_Exception) {
                $result['status'] = Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED;
                $result['error_code'] = $e->get_code();
                $result['messages'][] = __($e->get_message());
            } else {
                $result['status'] = Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                $result['error_code'] = $e->get_code();
                $result['messages'][] = __('Sorry, something went wrong during product prices update. Please see log for details.');
            }
        } catch (No_Such_Entity_Exception|Localized_Exception|\Exception $e) {
            $this->logger->error($e->get_message());
            $result['status'] = Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $result['error_code'] = $e->get_code();
            $result['messages'][] = $e->get_message();
        }
        return $result;
    }
}