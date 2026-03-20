<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\Amqp\Config_Pool;
use Magento\Framework\Amqp\Connection_Type_Resolver;
use Magento\Framework\Amqp\Topology\Exchange_Installer;
use Magento\Framework\Amqp\Topology\Queue_Installer;
use Magento\Framework\Amqp\Topology_Installer;
use Magento\Framework\Communication\Config_Interface as CommunicationConfig;
use Magento\Framework\Message_Queue\Config_Interface as QueueConfig;
use Magento\Framework\Message_Queue\Topology\Config_Interface as TopologyConfig;
/**
 * Class Topology creates topology for Amqp messaging
 *
 * @deprecated 100.2.0
 * @see Magento\Framework\MessageQueue
 */
class Topology extends Topology_Installer
{
    /**
     * Type of exchange
     *
     * @deprecated
     * @see not used anymore
     */
    public const TOPIC_EXCHANGE = 'topic';
    public const AMQP_CONNECTION = 'amqp';
    /**
     * Durability for exchange and queue
     *
     * @deprecated
     * @see not used anymore
     */
    public const IS_DURABLE = true;
    /**
     * Initialize dependencies
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Config $amqp_config, Queue_Config $queue_config, Communication_Config $communication_config, \Psr\Log\Logger_Interface $logger)
    {
        parent::__construct(\Magento\Framework\App\Object_Manager::get_instance()->get(Topology_Config::class), \Magento\Framework\App\Object_Manager::get_instance()->get(Exchange_Installer::class), \Magento\Framework\App\Object_Manager::get_instance()->get(Config_Pool::class), \Magento\Framework\App\Object_Manager::get_instance()->get(Queue_Installer::class), \Magento\Framework\App\Object_Manager::get_instance()->get(Connection_Type_Resolver::class), $logger);
    }
}