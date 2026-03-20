<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters;

/**
 * Interface for backend-specific tag operations
 *
 * This interface defines operations that different cache backends implement differently,
 * particularly for tag-based cache invalidation with AND logic (MATCHING_TAG mode).
 *
 * Implementations:
 * - RedisTagAdapter: Uses Redis SINTER for true AND logic
 * - FilesystemTagAdapter: Uses file-based tag indices with array_intersect
 * - GenericTagAdapter: Fallback using namespace tags or best-effort logic
 */
interface Tag_Adapter_Interface
{
    /**
     * Get cache IDs that match ALL given tags (AND logic)
     *
     * This is used for CacheConstants::CLEANING_MODE_MATCHING_TAG
     *
     * @param array $tags Array of tags (must match ALL)
     * @return array Array of cache IDs
     */
    public function get_ids_matching_tags(array $tags): array;
    /**
     * Get cache IDs that match ANY of the given tags (OR logic)
     *
     * This is used for CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG
     *
     * @param array $tags Array of tags (match ANY)
     * @return array Array of cache IDs
     */
    public function get_ids_matching_any_tags(array $tags): array;
    /**
     * Get cache IDs that do NOT match any of the given tags
     *
     * This is used for CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG
     *
     * @param array $tags Array of tags to exclude
     * @return array Array of cache IDs
     */
    public function get_ids_not_matching_tags(array $tags): array;
    /**
     * Delete cache items by their IDs
     *
     * @param array $ids Array of cache IDs to delete
     * @return bool True on success
     */
    public function delete_by_ids(array $ids): bool;
    /**
     * Update tag-to-ID index when a cache item is saved
     *
     * @param string $id Cache ID
     * @param array $tags Tags associated with this ID
     * @return void
     */
    public function on_save(string $id, array $tags): void;
    /**
     * Update tag-to-ID index when a cache item is removed
     *
     * @param string $id Cache ID
     * @return void
     */
    public function on_remove(string $id): void;
    /**
     * Clear all tag indices (used for CLEANING_MODE_ALL)
     *
     * @return void
     */
    public function clear_all_indices(): void;
}