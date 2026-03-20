<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Backpressure\Sliding_Window;

use Magento\Framework\App\Backpressure\Context_Interface;
use Magento\Framework\App\Backpressure\Sliding_Window\Redis_Request_Logger\Redis_Client;
use Magento\Framework\App\Deployment_Config;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\RuntimeException;
/**
 * Logging requests to Redis
 */
class Redis_Request_Logger implements Request_Logger_Interface
{
    /**
     * Identifier for Redis Logger type
     */
    public const BACKPRESSURE_LOGGER_REDIS = 'redis';
    /**
     * Default prefix id
     */
    private const DEFAULT_PREFIX_ID = 'reqlog';
    /**
     * Config path for backpressure logger id prefix
     */
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX = 'backpressure/logger/id-prefix';
    /**
     * @var RedisClient
     */
    private $redis_client;
    /**
     * @var DeploymentConfig
     */
    private $deployment_config;
    /**
     * @param RedisClient $redisClient
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(Redis_Client $redis_client, Deployment_Config $deployment_config)
    {
        $this->redis_client = $redis_client;
        $this->deployment_config = $deployment_config;
    }
    /**
     * @inheritDoc
     */
    public function incr_and_get_for(Context_Interface $context, int $time_slot, int $discard_after): int
    {
        $id = $this->generate_id($context, $time_slot);
        $this->redis_client->pipeline();
        $this->redis_client->incr_by($id, 1);
        $this->redis_client->expire_at($id, time() + $discard_after);
        return (int) $this->redis_client->exec()[0];
    }
    /**
     * @inheritDoc
     */
    public function get_for(Context_Interface $context, int $time_slot): ?int
    {
        $value = $this->redis_client->get($this->generate_id($context, $time_slot));
        return $value ? (int) $value : null;
    }
    /**
     * Generate cache ID based on context
     *
     * @param ContextInterface $context
     * @param int $timeSlot
     * @return string
     */
    private function generate_id(Context_Interface $context, int $time_slot): string
    {
        return $this->get_prefix_id() . $context->get_type_id() . $context->get_identity_type() . $context->get_identity() . $time_slot;
    }
    /**
     * Returns prefix id
     *
     * @return string
     */
    private function get_prefix_id(): string
    {
        try {
            return (string) $this->deployment_config->get(self::CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX, self::DEFAULT_PREFIX_ID);
        } catch (RuntimeException|File_System_Exception $e) {
            return self::DEFAULT_PREFIX_ID;
        }
    }
}