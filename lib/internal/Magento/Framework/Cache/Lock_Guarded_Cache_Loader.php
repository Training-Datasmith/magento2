<?php

declare (strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Lock\Lock_Manager_Interface;
/**
 * Default mutex that provide concurrent access to cache storage.
 */
class Lock_Guarded_Cache_Loader
{
    /**
     * @var LockManagerInterface
     */
    private $locker;
    /**
     * Lifetime of the lock for write in cache.
     *
     * Value of the variable in milliseconds.
     *
     * @var int
     */
    private $lock_timeout;
    /**
     * Timeout between retrieves to load the configuration from the cache.
     *
     * Value of the variable in milliseconds.
     *
     * @var int
     */
    private $delay_timeout;
    /**
     * Timeout for information to be collected and saved.
     * If timeout passed that means that data cannot be saved right now.
     * And we will just return collected data.
     *
     * Value of the variable in milliseconds.
     *
     * @var int
     */
    private $load_timeout;
    /**
     * Minimal delay timeout in ms.
     *
     * @var int
     */
    private $minimal_delay_timeout;
    /**
     * @var DeploymentConfig
     */
    private $deployment_config;
    /**
     * Option that allows to switch off blocking for parallel generation.
     *
     * @var string
     */
    private const CONFIG_PATH_ALLOW_PARALLEL_CACHE_GENERATION = 'cache/allow_parallel_generation';
    /**
     * Config value of parallel generation.
     *
     * @var bool|null
     */
    private ?bool $allow_parallel_generation_config_value;
    /**
     * @param LockManagerInterface $locker
     * @param int $lockTimeout
     * @param int $delayTimeout
     * @param int $loadTimeout
     * @param int $minimalDelayTimeout
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(Lock_Manager_Interface $locker, int $lock_timeout = 10000, int $delay_timeout = 20, int $load_timeout = 10000, int $minimal_delay_timeout = 5, ?Deployment_Config $deployment_config = null)
    {
        $this->locker = $locker;
        $this->lock_timeout = $lock_timeout;
        $this->delay_timeout = $delay_timeout;
        $this->load_timeout = $load_timeout;
        $this->minimal_delay_timeout = $minimal_delay_timeout;
        $this->deployment_config = $deployment_config ?? Object_Manager::get_instance()->get(Deployment_Config::class);
    }
    /**
     * Load data.
     *
     * @param string $lockName
     * @param callable $dataLoader
     * @param callable $dataCollector
     * @param callable $dataSaver
     * @return mixed
     */
    public function locked_load_data(string $lock_name, callable $data_loader, callable $data_collector, callable $data_saver)
    {
        $cached_data = $data_loader();
        //optimistic read
        $deadline = microtime(true) + $this->load_timeout / 1000;
        if (empty($this->allow_parallel_generation_config_value)) {
            $this->allow_parallel_generation_config_value = (bool) $this->deployment_config->get(self::CONFIG_PATH_ALLOW_PARALLEL_CACHE_GENERATION);
        }
        while ($cached_data === false) {
            if ($deadline <= microtime(true)) {
                return $data_collector();
            }
            if ($this->locker->lock($lock_name, 0)) {
                try {
                    $data = $data_collector();
                    $data_saver($data);
                    $cached_data = $data;
                } finally {
                    $this->locker->unlock($lock_name);
                }
            } elseif ($this->allow_parallel_generation_config_value) {
                return $data_collector();
            }
            if ($cached_data === false) {
                usleep($this->get_lookup_timeout() * 1000);
                $cached_data = $data_loader();
            }
        }
        return $cached_data;
    }
    /**
     * Clean data.
     *
     * @param string $lockName
     * @param callable $dataCleaner
     * @return void
     */
    public function locked_clean_data(string $lock_name, callable $data_cleaner)
    {
        while ($this->locker->is_locked($lock_name)) {
            usleep($this->get_lookup_timeout() * 1000);
        }
        $data_cleaner();
    }
    /**
     * Delay will be applied as rand($minimalDelayTimeout, $delayTimeout).
     * This helps to desynchronize multiple clients trying
     * to acquire the lock for the same resource at the same time
     *
     * @return int
     */
    private function get_lookup_timeout()
    {
        return rand($this->minimal_delay_timeout, $this->delay_timeout);
    }
}