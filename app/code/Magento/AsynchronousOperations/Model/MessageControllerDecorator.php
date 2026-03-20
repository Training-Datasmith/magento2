<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
use Magento\Asynchronous_Operations\Model\Config_Interface as AsyncConfig;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Message_Queue\Envelope_Interface;
use Magento\Framework\Message_Queue\Lock_Interface;
use Magento\Framework\Message_Queue\Message_Controller;
use Magento\Framework\Message_Queue\Message_Encoder;
use Magento\Framework\Message_Queue\Message_Validator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Throwable;
/**
 * Decorator for MessageController
 */
class Message_Controller_Decorator
{
    public function __construct(private readonly Resource_Connection $resource, private readonly Message_Controller $message_controller, private readonly Message_Validator $message_validator, private readonly Message_Encoder $message_encoder, private readonly Metadata_Pool $metadata_pool, private readonly DateTime $date_time)
    {
    }
    /**
     * Creates lock for provided message and update the operation start time
     */
    public function lock(Envelope_Interface $envelope, string $consumer_name): Lock_Interface
    {
        $operation = $this->message_encoder->decode(Async_Config::SYSTEM_TOPIC_NAME, $envelope->get_body());
        $this->message_validator->validate(Async_Config::SYSTEM_TOPIC_NAME, $operation);
        $metadata = $this->metadata_pool->get_metadata(Operation_Interface::class);
        $connection = $this->resource->get_connection($metadata->get_entity_connection_name());
        $connection->begin_transaction();
        try {
            $lock = $this->message_controller->lock($envelope, $consumer_name);
            $connection->update($metadata->get_entity_table(), ['started_at' => $connection->format_date($this->date_time->gmt_timestamp())], ['bulk_uuid = ?' => $operation->get_bulk_uuid(), 'operation_key = ?' => $operation->get_id()]);
            $connection->commit();
        } catch (Throwable $exception) {
            $connection->roll_back();
            throw $exception;
        }
        return $lock;
    }
}