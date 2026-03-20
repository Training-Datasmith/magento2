<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\Message_Queue\Connection_Type_Resolver_Interface;
/**
 * Amqp connection type resolver.
 *
 * @api
 * @since 103.0.0
 */
class Connection_Type_Resolver implements Connection_Type_Resolver_Interface
{
    /**
     * Amqp connection names.
     *
     * @var string[]
     */
    private $amqp_connection_name = [];
    /**
     * Initialize dependencies.
     *
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(Deployment_Config $deployment_config)
    {
        $queue_config = $deployment_config->get_config_data(Config::QUEUE_CONFIG);
        if (isset($queue_config['connections']) && is_array($queue_config['connections'])) {
            $this->amqp_connection_name = array_keys($queue_config['connections']);
        }
        if (isset($queue_config[Config::AMQP_CONFIG])) {
            $this->amqp_connection_name[] = Config::AMQP_CONFIG;
        }
    }
    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function get_connection_type($connection_name)
    {
        return in_array($connection_name, $this->amqp_connection_name, true) ? 'amqp' : null;
    }
}