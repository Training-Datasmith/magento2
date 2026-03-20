<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Model\Config_Interface as AsyncConfig;
use Magento\Framework\Message_Queue\Bulk\Exchange_Repository;
use Magento\Framework\Message_Queue\Envelope_Factory;
use Magento\Framework\Message_Queue\Message_Encoder;
use Magento\Framework\Message_Queue\Message_Id_Generator_Interface;
use Magento\Framework\Message_Queue\Message_Validator;
use Magento\Framework\Message_Queue\Publisher\Config_Interface as PublisherConfig;
use Magento\Framework\Message_Queue\Publisher_Interface;
/**
 * Class MassPublisher used for encoding topic entities to OperationInterface and publish them.
 */
class Mass_Publisher implements Publisher_Interface
{
    /**
     * Initialize dependencies.
     */
    public function __construct(private readonly Exchange_Repository $exchange_repository, private readonly Envelope_Factory $envelope_factory, private readonly Message_Encoder $message_encoder, private readonly Message_Validator $message_validator, private readonly Publisher_Config $publisher_config, private readonly Message_Id_Generator_Interface $message_id_generator)
    {
    }
    /**
     * @inheritdoc
     */
    public function publish($topic_name, $data): null
    {
        $envelopes = [];
        foreach ($data as $message) {
            $this->message_validator->validate(Async_Config::SYSTEM_TOPIC_NAME, $message);
            $message = $this->message_encoder->encode(Async_Config::SYSTEM_TOPIC_NAME, $message);
            $envelopes[] = $this->envelope_factory->create(['body' => $message, 'properties' => ['topic_name' => $topic_name, 'delivery_mode' => 2, 'message_id' => $this->message_id_generator->generate($topic_name)]]);
        }
        $publisher = $this->publisher_config->get_publisher($topic_name);
        $connection_name = $publisher->get_connection()->get_name();
        $exchange = $this->exchange_repository->get_by_connection_name($connection_name);
        $exchange->enqueue($topic_name, $envelopes);
        return null;
    }
}