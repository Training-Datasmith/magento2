<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Topology\Exchange_Installer;
use Magento\Framework\Amqp\Topology\Queue_Installer;
use Magento\Framework\Message_Queue\Topology\Config_Interface;
/**
 * Class Topology creates topology for Amqp messaging
 */
class Topology_Installer
{
    /**
     * @var ConfigInterface
     */
    private $topology_config;
    /**
     * @var \Magento\Framework\Amqp\Topology\ExchangeInstaller
     */
    private $exchange_installer;
    /**
     * @var ConfigPool
     */
    private $config_pool;
    /**
     * @var \Magento\Framework\Amqp\Topology\QueueInstaller
     */
    private $queue_installer;
    /**
     * @var ConnectionTypeResolver
     */
    private $connection_type_resolver;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $topologyConfig
     * @param ExchangeInstaller $exchangeInstaller
     * @param ConfigPool $configPool
     * @param QueueInstaller $queueInstaller
     * @param ConnectionTypeResolver $connectionTypeResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(Config_Interface $topology_config, Exchange_Installer $exchange_installer, Config_Pool $config_pool, Queue_Installer $queue_installer, Connection_Type_Resolver $connection_type_resolver, \Psr\Log\Logger_Interface $logger)
    {
        $this->topology_config = $topology_config;
        $this->exchange_installer = $exchange_installer;
        $this->config_pool = $config_pool;
        $this->queue_installer = $queue_installer;
        $this->connection_type_resolver = $connection_type_resolver;
        $this->logger = $logger;
    }
    /**
     * Install Amqp Exchanges, Queues and bind them
     *
     * @return void
     */
    public function install()
    {
        try {
            foreach ($this->topology_config->get_queues() as $queue) {
                if ($this->connection_type_resolver->get_connection_type($queue->get_connection()) != 'amqp') {
                    continue;
                }
                $amqp_config = $this->config_pool->get($queue->get_connection());
                $this->queue_installer->install($amqp_config->get_channel(), $queue);
            }
            foreach ($this->topology_config->get_exchanges() as $exchange) {
                if ($this->connection_type_resolver->get_connection_type($exchange->get_connection()) != 'amqp') {
                    continue;
                }
                $amqp_config = $this->config_pool->get($exchange->get_connection());
                $this->exchange_installer->install($amqp_config->get_channel(), $exchange);
            }
        } catch (\Exception $e) {
            $this->logger->error("AMQP topology installation failed: {$e->get_message()}\n{$e->get_trace_as_string()}");
        }
    }
}