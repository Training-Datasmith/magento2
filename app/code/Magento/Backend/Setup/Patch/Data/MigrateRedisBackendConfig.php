<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Setup\Patch\Data;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Deployment_Config\Writer;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Setup\Patch\Data_Patch_Interface;
/**
 * Migrate Redis backend configuration from full class names to simple identifiers
 *
 * Automatically updates env.php during setup:upgrade:
 * - 'Magento\\Framework\\Cache\\Backend\\Redis' → 'redis'
 * - 'Magento\\Framework\\Cache\\Backend\\Valkey' → 'valkey'
 */
class Migrate_Redis_Backend_Config implements Data_Patch_Interface
{
    /**
     * @var Writer
     */
    private Writer $config_writer;
    /**
     * @var DeploymentConfig
     */
    private Deployment_Config $deployment_config;
    /**
     * @param Writer $configWriter
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(Writer $config_writer, Deployment_Config $deployment_config)
    {
        $this->config_writer = $config_writer;
        $this->deployment_config = $deployment_config;
    }
    /**
     * @inheritDoc
     */
    public function apply()
    {
        // Migration map: Legacy full class name strings => new simple identifiers
        // These are not actual classes - they're legacy string identifiers in env.php
        $migration_map = [
            // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
            'Magento\Framework\Cache\Backend\Redis' => 'redis',
            // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
            'Magento\Framework\Cache\Backend\Valkey' => 'valkey',
        ];
        // Get current cache configuration from env.php
        $cache_config = $this->deployment_config->get('cache');
        if (!$cache_config || !isset($cache_config['frontend'])) {
            // No cache frontend configuration - nothing to migrate
            return $this;
        }
        $config_updates = [];
        $migrated = false;
        // Check and migrate each cache frontend
        foreach ($cache_config['frontend'] as $frontend_name => $frontend_config) {
            if (isset($frontend_config['backend']) && isset($migration_map[$frontend_config['backend']])) {
                $old_value = $frontend_config['backend'];
                $new_value = $migration_map[$old_value];
                if (!isset($config_updates['cache'])) {
                    $config_updates['cache'] = $cache_config;
                }
                $config_updates['cache']['frontend'][$frontend_name]['backend'] = $new_value;
                $migrated = true;
            }
        }
        // Apply updates if any migrations occurred
        if ($migrated) {
            $this->config_writer->save_config([Config_File_Pool::APP_ENV => $config_updates], true);
        }
        return $this;
    }
    /**
     * @inheritDoc
     */
    public static function get_dependencies()
    {
        return [];
    }
    /**
     * @inheritDoc
     */
    public function get_aliases()
    {
        return [];
    }
}