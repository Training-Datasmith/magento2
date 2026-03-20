<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters\Tag_Adapter_Interface;
/**
 * Low-level backend wrapper for Symfony cache adapter
 *
 * Provides backend-level methods for tag operations and cache cleaning
 * Used by tests and utilities that need direct backend access
 */
class Low_Level_Backend
{
    /**
     * @var TagAdapterInterface
     */
    private Tag_Adapter_Interface $adapter;
    /**
     * @param TagAdapterInterface $adapter
     */
    public function __construct(Tag_Adapter_Interface $adapter)
    {
        $this->adapter = $adapter;
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
            return $this->adapter->get_ids_matching_tags($tags);
        }
        return [];
    }
    /**
     * Clean cache entries
     *
     * @param string $mode
     * @param array $tags
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, array $tags = []): bool
    {
        // Backend clean is handled by adapter
        if ($mode === Cache_Constants::CLEANING_MODE_ALL) {
            if (method_exists($this->adapter, 'clearAllIndices')) {
                $this->adapter->clear_all_indices();
            }
        }
        return true;
    }
}