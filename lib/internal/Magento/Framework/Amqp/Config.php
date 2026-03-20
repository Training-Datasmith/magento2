<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Amqp\Connection\Factory_Options;
use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Php_Amqp_Lib\Channel\Amqp_Channel;
use Php_Amqp_Lib\Connection\Abstract_Connection;
/**
 * Reads the Amqp config in the deployed environment configuration
 *
 * @api
 * @since 103.0.0
 */
class Config implements Reset_After_Request_Interface
{
    /**
     * Queue config key
     */
    public const QUEUE_CONFIG = 'queue';
    /**
     * Amqp config key
     */
    public const AMQP_CONFIG = 'amqp';
    public const HOST = 'host';
    public const PORT = 'port';
    public const USERNAME = 'user';
    public const PASSWORD = 'password';
    public const VIRTUALHOST = 'virtualhost';
    public const SSL = 'ssl';
    public const SSL_OPTIONS = 'ssl_options';
    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deployment_config;
    /**
     * @var AbstractConnection
     */
    private $connection;
    /**
     * @var AMQPChannel
     */
    private $channel;
    /**
     * Associative array of Amqp configuration
     *
     * @var array
     */
    private $data;
    /**
     * AMQP connection name.
     *
     * @var string
     */
    private $connection_name;
    /**
     * @var ConnectionFactory
     */
    private $connection_factory;
    /**
     * Initialize dependencies.
     *
     * Example environment config:
     * <code>
     * 'queue' =>
     *     [
     *         'amqp' => [
     *             'host' => 'localhost',
     *             'port' => 5672,
     *             'username' => 'guest',
     *             'password' => 'guest',
     *             'virtual_host' => '/',
     *             'ssl' => false,
     *             'ssl_options' => [],
     *         ],
     *     ],
     * </code>
     *
     * @param DeploymentConfig $config
     * @param string $connectionName
     * @param ConnectionFactory|null $connectionFactory
     */
    public function __construct(Deployment_Config $config, $connection_name = 'amqp', ?Connection_Factory $connection_factory = null)
    {
        $this->deployment_config = $config;
        $this->connection_name = $connection_name;
        $this->connection_factory = $connection_factory ?: Object_Manager::get_instance()->get(Connection_Factory::class);
    }
    /**
     * Destructor
     *
     * @return void
     * @since 103.0.0
     */
    public function __destruct()
    {
        try {
            $this->close_connection();
        } catch (\Throwable $e) {
            error_log($e->get_message());
        }
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->close_connection();
    }
    /**
     * Returns the configuration set for the key.
     *
     * @param string $key
     * @return string
     * @throws \LogicException
     * @since 103.0.0
     */
    public function get_value($key)
    {
        $this->load();
        return $this->data[$key] ?? null;
    }
    /**
     * Create amqp connection
     *
     * @return AbstractConnection
     */
    private function create_connection(): Abstract_Connection
    {
        $ssl_enabled = trim($this->get_value(self::SSL) ?? '') === 'true';
        $options = new Factory_Options();
        $options->set_host($this->get_value(self::HOST));
        $options->set_port($this->get_value(self::PORT));
        $options->set_username($this->get_value(self::USERNAME));
        $options->set_password($this->get_value(self::PASSWORD));
        $options->set_virtual_host($this->get_value(self::VIRTUALHOST));
        $options->set_ssl_enabled($ssl_enabled);
        /** @var array $sslOptions */
        if ($ssl_options = $this->get_value(self::SSL_OPTIONS)) {
            $options->set_ssl_options($ssl_options);
        }
        return $this->connection_factory->create($options);
    }
    /**
     * Return Amqp channel
     *
     * @return AMQPChannel
     * @throws \LogicException
     * @since 103.0.0
     */
    public function get_channel()
    {
        if (!isset($this->connection)) {
            $this->connection = $this->create_connection();
        }
        if (!isset($this->channel) || !$this->channel->get_connection() || !$this->channel->get_connection()->is_connected()) {
            if (!$this->connection->is_connected()) {
                $this->connection->reconnect();
            }
            $this->channel = $this->connection->channel();
        }
        return $this->channel;
    }
    /**
     * Load the configuration for Amqp
     *
     * @return void
     * @throws \LogicException
     */
    private function load()
    {
        if (null === $this->data) {
            $queue_config = $this->deployment_config->get_config_data(self::QUEUE_CONFIG);
            if ($this->connection_name == self::AMQP_CONFIG) {
                $this->data = isset($queue_config[self::AMQP_CONFIG]) ? $queue_config[self::AMQP_CONFIG] : [];
            } else {
                $this->data = isset($queue_config['connections'][$this->connection_name]) ? $queue_config['connections'][$this->connection_name] : [];
            }
            if (empty($this->data)) {
                throw new \LogicException('Unknown connection name ' . $this->connection_name);
            }
        }
    }
    /**
     * Close Amqp connection and Channel
     *
     * @return void
     */
    private function close_connection()
    {
        if (isset($this->channel)) {
            $this->channel->close();
            unset($this->channel);
        }
        if (isset($this->connection)) {
            $this->connection->close();
            unset($this->connection);
        }
    }
    /**
     * Get connection name
     *
     * @return string
     */
    public function get_connection_name(): string
    {
        return $this->connection_name;
    }
}