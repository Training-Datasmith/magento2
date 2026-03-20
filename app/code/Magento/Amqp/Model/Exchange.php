<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\Communication\Config_Interface as CommunicationConfigInterface;
use Magento\Framework\Message_Queue\Config_Interface as QueueConfig;
use Magento\Framework\Message_Queue\Publisher\Config_Interface as PublisherConfig;
use Magento\Framework\Message_Queue\Rpc\Response_Queue_Name_Builder;
/**
 * {@inheritdoc}
 *
 * @deprecated 100.2.0
 * @see Magento\Framework\MessageQueue
 */
class Exchange extends \Magento\Framework\Amqp\Exchange
{
    /**
     * Initialize dependencies.
     *
     * @param int $rpcConnectionTimeout
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Config $amqp_config, Queue_Config $queue_config, Communication_Config_Interface $communication_config, $rpc_connection_timeout = self::RPC_CONNECTION_TIMEOUT)
    {
        parent::__construct($amqp_config, $this->get_publisher_config(), $this->get_response_queue_name_builder(), $communication_config, $rpc_connection_timeout);
    }
    /**
     * Get publisher config.
     *
     * @return PublisherConfig
     *
     * @deprecated 100.2.0
     * @see it's a private method, not used anymore
     */
    private function get_publisher_config()
    {
        return \Magento\Framework\App\Object_Manager::get_instance()->get(Publisher_Config::class);
    }
    /**
     * Get response queue name builder.
     *
     * @return ResponseQueueNameBuilder
     *
     * @deprecated 100.2.0
     * @see it's a private method, not used anymore
     */
    private function get_response_queue_name_builder()
    {
        return \Magento\Framework\App\Object_Manager::get_instance()->get(Response_Queue_Name_Builder::class);
    }
}