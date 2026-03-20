<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config\File;

/**
 * Stores file key to file name config
 * @api
 * @since 100.0.2
 */
class Config_File_Pool
{
    public const APP_CONFIG = 'app_config';
    public const APP_ENV = 'app_env';
    /**
     * @deprecated Magento does not support custom config file pools since 2.2.0 version
     */
    public const LOCAL = 'local';
    /**
     * @deprecated Magento does not support custom config file pools since 2.2.0 version
     */
    public const DIST = 'dist';
    /**
     * Default files for configuration
     *
     * @var array
     */
    private $application_config_files = [self::APP_CONFIG => 'config.php', self::APP_ENV => 'env.php'];
    /**
     * Initial files for configuration
     *
     * @var array
     * @deprecated 101.0.0 Magento does not support custom config file pools since 2.2.0 version
     */
    private $initial_config_files = [self::DIST => [self::APP_CONFIG => 'config.dist.php', self::APP_ENV => 'env.dist.php'], self::LOCAL => [self::APP_CONFIG => 'config.local.php', self::APP_ENV => 'env.local.php']];
    /**
     * Constructor
     *
     * @param array $additionalConfigFiles
     */
    public function __construct($additional_config_files = [])
    {
        $this->application_config_files = array_merge($this->application_config_files, $additional_config_files);
    }
    /**
     * Returns application config files.
     *
     * @return array
     */
    public function get_paths()
    {
        return $this->application_config_files;
    }
    /**
     * Returns file path by config key
     *
     * @param string $fileKey
     * @return string
     * @throws \Exception
     */
    public function get_path($file_key)
    {
        if (!isset($this->application_config_files[$file_key])) {
            throw new \Exception('File config key does not exist.');
        }
        return $this->application_config_files[$file_key];
    }
    /**
     * Returns application initial config files.
     *
     * @return array
     * @deprecated 101.0.0 Magento does not support custom config file pools since 2.2.0 version
     * @since 100.1.3
     */
    public function get_initial_file_pools()
    {
        return $this->initial_config_files;
    }
    /**
     * Retrieve all config file pools.
     *
     * @param string $pool
     * @return array
     * @deprecated 101.0.0 Magento does not support custom config file pools since 2.2.0 version
     * @since 100.1.3
     */
    public function get_paths_by_pool($pool)
    {
        return $this->initial_config_files[$pool];
    }
}