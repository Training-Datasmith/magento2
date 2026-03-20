<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters\Tag_Adapter_Interface;
use Magento\Framework\Cache\Frontend_Interface;
use Psr\Cache\Cache_Item_Pool_Interface;
/**
 * Low-level frontend wrapper for Symfony cache adapter
 *
 * Provides backward-compatible interface for legacy code
 * Used by code that needs direct access to cache internals
 */
class Low_Level_Frontend
{
    /**
     * @var CacheItemPoolInterface
     */
    private Cache_Item_Pool_Interface $cache;
    /**
     * @var FrontendInterface
     */
    private Frontend_Interface $symfony;
    /**
     * @var TagAdapterInterface
     */
    private Tag_Adapter_Interface $adapter;
    /**
     * @var string
     */
    private string $id_prefix;
    /**
     * @var int
     */
    private int $lifetime;
    /**
     * @var LowLevelBackend|null
     */
    private ?Low_Level_Backend $backend = null;
    /**
     * @param CacheItemPoolInterface $cache
     * @param FrontendInterface $symfony
     * @param TagAdapterInterface $adapter
     * @param string $idPrefix
     * @param int $lifetime
     */
    public function __construct(Cache_Item_Pool_Interface $cache, Frontend_Interface $symfony, Tag_Adapter_Interface $adapter, string $id_prefix, int $lifetime = 7200)
    {
        $this->cache = $cache;
        $this->symfony = $symfony;
        $this->adapter = $adapter;
        $this->id_prefix = $id_prefix;
        $this->lifetime = $lifetime;
    }
    /**
     * Get metadata for cache entry
     *
     * @param string $id
     * @return array|false
     */
    public function get_metadatas($id)
    {
        return $this->symfony->get_metadatas($id);
    }
    /**
     * Get cache option
     *
     * @param string $name
     * @return mixed
     */
    public function get_option(string $name)
    {
        if ($name === 'cache_id_prefix') {
            return $this->id_prefix;
        }
        if ($name === 'lifetime') {
            return $this->lifetime;
        }
        return null;
    }
    /**
     * Get IDs matching tags
     *
     * @param array $tags
     * @return array
     */
    public function get_ids_matching_tags(array $tags): array
    {
        // Get IDs from helper (uses backend-specific logic)
        if (method_exists($this->adapter, 'getIdsMatchingTags')) {
            // Tags are already in the correct format from the caller
            // Helper will add namespace prefix internally
            return $this->adapter->get_ids_matching_tags($tags);
        }
        // For GenericAdapterHelper, return empty array
        // (it doesn't support native ID lookup by tags)
        return [];
    }
    /**
     * Get backend wrapper
     *
     * @return LowLevelBackend
     */
    public function get_backend(): Low_Level_Backend
    {
        if ($this->backend === null) {
            $this->backend = new Low_Level_Backend($this->adapter);
        }
        return $this->backend;
    }
    /**
     * Clean cache entries
     *
     * Delegates to Symfony frontend adapter to ensure proper Lua integration
     *
     * @param string $mode Cleaning mode
     * @param array $tags Tags array
     * @return bool
     */
    public function clean($mode = 'all', array $tags = []): bool
    {
        // Delegate to Symfony frontend for proper Lua script integration
        return $this->symfony->clean($mode, $tags);
    }
    /**
     * Delegate all other method calls to the cache
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->cache->{$method}(...$arguments);
    }
}