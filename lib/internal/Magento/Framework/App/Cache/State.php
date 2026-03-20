<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Deployment_Config\Writer;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * Cache State
 */
class State implements State_Interface, Reset_After_Request_Interface
{
    /**
     * Disallow cache
     */
    public const PARAM_BAN_CACHE = 'global_ban_use_cache';
    /**
     * Deployment config key
     */
    public const CACHE_KEY = 'cache_types';
    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     *  phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly Deployment_Config $config;
    /**
     * Deployment configuration storage writer
     *
     * @var Writer
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly Writer $writer;
    /**
     * Associative array of cache type codes and their statuses (enabled/disabled)
     *
     * @var array|null
     */
    private ?array $statuses = null;
    /**
     * Whether all cache types are forced to be disabled
     *
     * @var bool
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly bool $ban_all;
    /**
     * Constructor
     *
     * @param DeploymentConfig $config
     * @param Writer $writer
     * @param bool $banAll
     */
    public function __construct(Deployment_Config $config, Writer $writer, $ban_all = false)
    {
        $this->config = $config;
        $this->writer = $writer;
        $this->ban_all = $ban_all;
    }
    /**
     * Whether a cache type is enabled or not at the moment
     *
     * @param string $cacheType
     * @return bool
     */
    public function is_enabled($cache_type): bool
    {
        $this->load();
        return (bool) ($this->statuses[$cache_type] ?? false);
    }
    /**
     * Enable/disable a cache type in run-time
     *
     * @param string $cacheType
     * @param bool $isEnabled
     * @return void
     */
    public function set_enabled($cache_type, $is_enabled): void
    {
        $this->load();
        $this->statuses[$cache_type] = (int) $is_enabled;
    }
    /**
     * Save the current statuses (enabled/disabled) of cache types to the persistent storage
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function persist(): void
    {
        $this->load();
        $this->writer->save_config([Config_File_Pool::APP_ENV => [self::CACHE_KEY => $this->statuses]]);
    }
    /**
     * Load statuses (enabled/disabled) of cache types
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function load(): void
    {
        if (null === $this->statuses) {
            $this->statuses = [];
            if ($this->ban_all) {
                return;
            }
            $this->statuses = $this->config->get_config_data(self::CACHE_KEY) ?: [];
        }
    }
    /**
     * @inheritdoc
     */
    public function _reset_state(): void
    {
        $this->statuses = null;
    }
}