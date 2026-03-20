<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp;

/**
 * AMQP connections pool.
 */
class Config_Pool
{
    /**
     * @var ConfigFactory
     */
    private $config_factory;
    /**
     * @var Config[]
     */
    private $pool = [];
    /**
     * Initialize dependencies.
     *
     * @param ConfigFactory $configFactory
     */
    public function __construct(Config_Factory $config_factory)
    {
        $this->config_factory = $config_factory;
    }
    /**
     * Get connection by name.
     *
     * @param string $connectionName
     * @return Config
     */
    public function get($connection_name)
    {
        if (!isset($this->pool[$connection_name])) {
            $this->pool[$connection_name] = $this->config_factory->create(['connectionName' => $connection_name]);
        }
        return $this->pool[$connection_name];
    }
    /**
     * Close all opened connections.
     *
     * @return void
     */
    public function close_connections(): void
    {
        foreach ($this->pool as $config) {
            $connection = $config->get_channel()->get_connection();
            $config->get_channel()->close();
            $connection?->close();
        }
    }
}