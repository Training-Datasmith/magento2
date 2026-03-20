<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure\Sliding_Window\Redis_Request_Logger;

use Credis_Client;
use Magento\Framework\App\Deployment_Config;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\RuntimeException;
/**
 * Redis client for request logger
 */
class Redis_Client
{
    /**
     * Keys for Redis settings
     */
    public const KEY_HOST = 'host';
    public const KEY_PORT = 'port';
    public const KEY_TIMEOUT = 'timeout';
    public const KEY_PERSISTENT = 'persistent';
    public const KEY_DB = 'db';
    public const KEY_PASSWORD = 'password';
    public const KEY_USER = 'user';
    /**
     * Configuration paths for Redis settings
     */
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER = 'backpressure/logger/options/server';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT = 'backpressure/logger/options/port';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT = 'backpressure/logger/options/timeout';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT = 'backpressure/logger/options/persistent';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB = 'backpressure/logger/options/db';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD = 'backpressure/logger/options/password';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER = 'backpressure/logger/options/user';
    /**
     * Redis default settings
     */
    public const DEFAULT_REDIS_CONFIG_VALUES = [self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER => '127.0.0.1', self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT => 6379, self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT => null, self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT => '', self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB => 3, self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD => null, self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER => null];
    /**
     * Config map
     */
    public const KEY_CONFIG_PATH_MAP = [self::KEY_HOST => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER, self::KEY_PORT => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT, self::KEY_TIMEOUT => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT, self::KEY_PERSISTENT => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT, self::KEY_DB => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB, self::KEY_PASSWORD => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD, self::KEY_USER => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER];
    /**
     * @var Credis_Client
     */
    private $client;
    /**
     * @param DeploymentConfig $config
     */
    public function __construct(Deployment_Config $config)
    {
        $this->client = new Credis_Client($this->get_host($config), $this->get_port($config), $this->get_timeout($config), $this->get_persistent($config), $this->get_db($config), $this->get_password($config), $this->get_user($config));
    }
    /**
     * Increments given key value
     *
     * @param string $key
     * @param int $decrement
     * @return Credis_Client|int
     */
    public function incr_by(string $key, int $decrement)
    {
        return $this->client->incr_by($key, $decrement);
    }
    /**
     * Sets expiration date of the key
     *
     * @param string $key
     * @param int $timestamp
     * @return Credis_Client|int
     */
    public function expire_at(string $key, int $timestamp)
    {
        return $this->client->expire_at($key, $timestamp);
    }
    /**
     * Returns value by key
     *
     * @param string $key
     * @return bool|Credis_Client|string
     */
    public function get(string $key)
    {
        return $this->client->get($key);
    }
    /**
     * Start pipeline
     *
     * @return void
     */
    public function pipeline(): void
    {
        $this->client->pipeline();
    }
    /**
     * Execute statement
     *
     * @return array
     */
    public function exec(): array
    {
        return $this->client->exec();
    }
    /**
     * Returns Redis host
     *
     * @param DeploymentConfig $config
     * @return string
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function get_host(Deployment_Config $config): string
    {
        return $config->get(self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER, self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER]);
    }
    /**
     * Returns Redis port
     *
     * @param DeploymentConfig $config
     * @return int
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function get_port(Deployment_Config $config): int
    {
        return (int) $config->get(self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT, self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT]);
    }
    /**
     * Returns Redis timeout
     *
     * @param DeploymentConfig $config
     * @return float|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function get_timeout(Deployment_Config $config): ?float
    {
        return (float) $config->get(self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT, self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT]);
    }
    /**
     * Returns Redis persistent
     *
     * @param DeploymentConfig $config
     * @return string
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function get_persistent(Deployment_Config $config): string
    {
        return $config->get(self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT, self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT]);
    }
    /**
     * Returns Redis db
     *
     * @param DeploymentConfig $config
     * @return int
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function get_db(Deployment_Config $config): int
    {
        return (int) $config->get(self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB, self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB]);
    }
    /**
     * Returns Redis password
     *
     * @param DeploymentConfig $config
     * @return string|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function get_password(Deployment_Config $config): ?string
    {
        return $config->get(self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD, self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD]);
    }
    /**
     * Returns Redis user
     *
     * @param DeploymentConfig $config
     * @return string|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function get_user(Deployment_Config $config): ?string
    {
        return $config->get(self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER, self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER]);
    }
}