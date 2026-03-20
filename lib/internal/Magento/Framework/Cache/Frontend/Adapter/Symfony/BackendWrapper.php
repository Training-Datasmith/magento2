<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

use InvalidArgumentException;
use Magento\Framework\Cache\Backend\Backend_Interface;
use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters\Tag_Adapter_Interface;
use Magento\Framework\Cache\Frontend_Interface;
use Psr\Cache\Cache_Item_Pool_Interface;
/**
 * Backend wrapper for Symfony cache adapter
 *
 * Provides BackendInterface-compatible wrapper for Symfony PSR-6 cache.
 * Delegates operations to the Symfony frontend for proper tag and metadata handling.
 */
class Backend_Wrapper implements Backend_Interface
{
    /**
     * @var CacheItemPoolInterface
     */
    private Cache_Item_Pool_Interface $cache;
    /**
     * @var TagAdapterInterface
     */
    private Tag_Adapter_Interface $adapter;
    /**
     * @var FrontendInterface
     */
    private Frontend_Interface $symfony;
    /**
     * @param CacheItemPoolInterface $cache
     * @param TagAdapterInterface $adapter
     * @param FrontendInterface $symfony
     */
    public function __construct(Cache_Item_Pool_Interface $cache, Tag_Adapter_Interface $adapter, Frontend_Interface $symfony)
    {
        $this->cache = $cache;
        $this->adapter = $adapter;
        $this->symfony = $symfony;
    }
    /**
     * Test if a cache is available for the given id
     *
     * @param string $id Cache id
     * @return int|false Last modified timestamp if available, false otherwise
     */
    public function test($id)
    {
        return $this->symfony->test($id);
    }
    /**
     * Load value with given id from cache
     *
     * @param string $id Cache id
     * @param bool $doNotTestCacheValidity If true, validity not tested
     * @return string|false Cached data or false if not available
     */
    public function load($id, $do_not_test_cache_validity = false)
    {
        // Delegate to frontend (validity always tested in Symfony)
        return $this->symfony->load($id);
    }
    /**
     * Save some data in cache
     *
     * @param mixed $data Data to cache
     * @param string $id Cache id
     * @param array $tags Array of tags
     * @param int|null $specificLifetime Specific lifetime (null = infinite)
     * @return bool True if no problem
     */
    public function save($data, $id, $tags = [], $specific_lifetime = null)
    {
        // Delegate to frontend for full save logic
        return $this->symfony->save($data, $id, $tags, $specific_lifetime);
    }
    /**
     * Remove a cache record
     *
     * @param string $id Cache id
     * @return bool True if no problem
     */
    public function remove($id)
    {
        // Delegate to frontend
        return $this->symfony->remove($id);
    }
    /**
     * Clean some cache records
     *
     * @param string $mode Clean mode ('all', 'old')
     * @param array $tags Array of tags (unused for backend clean)
     * @return bool True if no problem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function clean($mode = 'all', $tags = [])
    {
        return match ($mode) {
            Cache_Constants::CLEANING_MODE_ALL, 'all' => $this->clear(),
            Cache_Constants::CLEANING_MODE_OLD, 'old' => true,
            default => throw new InvalidArgumentException('Backend clean only supports ALL and OLD modes'),
        };
    }
    /**
     * Set an option
     *
     * @param string $name Option name
     * @param mixed $value Option value
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
    public function set_option($name, $value)
    {
        // Intentional no-op: Symfony backend options are not stored in the wrapper
        // This method exists for BackendInterface compliance but does nothing
    }
    // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
    /**
     * Clear all cache entries
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->adapter->clear_all_indices();
        return $this->cache->clear();
    }
    /**
     * Get backend option
     *
     * @param string $name
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_option($name)
    {
        // Symfony backend options are not stored in the wrapper
        // This method exists for Zend compatibility but returns null
        return null;
    }
}