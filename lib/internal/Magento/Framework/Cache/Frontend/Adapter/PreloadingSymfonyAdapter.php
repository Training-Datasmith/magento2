<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Frontend_Interface;
/**
 * Preloading wrapper for Symfony cache adapter
 *
 * Preloads frequently accessed cache keys into local PHP memory on initialization
 * to eliminate Redis network roundtrips for critical configuration data.
 */
class Preloading_Symfony_Adapter implements Frontend_Interface
{
    /**
     * @var FrontendInterface
     */
    private Frontend_Interface $adapter;
    /**
     * @var array
     */
    private array $local_cache = [];
    /**
     * @var array
     */
    private array $preload_keys;
    /**
     * Constructor
     *
     * @param FrontendInterface $adapter Underlying cache adapter
     * @param array $preloadKeys List of cache key identifiers to preload
     */
    public function __construct(Frontend_Interface $adapter, array $preload_keys = [])
    {
        $this->adapter = $adapter;
        $this->preload_keys = $preload_keys;
        // Preload keys on initialization (one-time cost per worker)
        if (!empty($preload_keys)) {
            $this->preload_keys($preload_keys);
        }
    }
    /**
     * Preload specified keys from Redis into local memory
     *
     * @param array $keys
     * @return void
     */
    private function preload_keys(array $keys): void
    {
        foreach ($keys as $key) {
            $value = $this->adapter->load($key);
            if ($value !== false) {
                $this->local_cache[$key] = $value;
            }
        }
    }
    /**
     * @inheritDoc
     *
     * Checks local cache first before delegating to Redis
     */
    public function load($identifier)
    {
        // Fast path: check local cache first (0.0001ms)
        if (isset($this->local_cache[$identifier])) {
            return $this->local_cache[$identifier];
        }
        // Slow path: fetch from Redis (0.15ms)
        return $this->adapter->load($identifier);
    }
    /**
     * @inheritDoc
     *
     * Writes through to Redis (bypasses local cache to avoid stale data)
     */
    public function save($data, $identifier, array $tags = [], $life_time = null)
    {
        // Write through to Redis
        $result = $this->adapter->save($data, $identifier, $tags, $life_time);
        // If this is a preloaded key, update local cache
        if ($result && in_array($identifier, $this->preload_keys, true)) {
            $this->local_cache[$identifier] = $data;
        }
        return $result;
    }
    /**
     * @inheritDoc
     *
     * Delegates to underlying adapter
     */
    public function test($identifier)
    {
        return $this->adapter->test($identifier);
    }
    /**
     * @inheritDoc
     *
     * Delegates to underlying adapter and clears from local cache
     */
    public function remove($identifier)
    {
        // Remove from local cache if present
        unset($this->local_cache[$identifier]);
        return $this->adapter->remove($identifier);
    }
    /**
     * @inheritDoc
     *
     * Delegates to underlying adapter and clears local cache
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, array $tags = [])
    {
        // Clear local cache on clean operations
        $this->local_cache = [];
        // Re-preload after clean if needed
        $result = $this->adapter->clean($mode, $tags);
        if ($result && !empty($this->preload_keys)) {
            $this->preload_keys($this->preload_keys);
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function get_backend()
    {
        return $this->adapter->get_backend();
    }
    /**
     * @inheritDoc
     */
    public function get_low_level_frontend()
    {
        return $this->adapter->get_low_level_frontend();
    }
    /**
     * Get statistics about preloaded keys
     *
     * Useful for monitoring and debugging
     *
     * @return array
     */
    public function get_preload_stats(): array
    {
        return ['preload_keys_configured' => count($this->preload_keys), 'preload_keys_cached' => count($this->local_cache), 'cached_keys' => array_keys($this->local_cache)];
    }
}