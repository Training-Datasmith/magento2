<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters;

use Psr\Cache\Cache_Item_Pool_Interface;
/**
 * Generic tag adapter for backends that don't support native tag-to-ID indices
 *
 * This is a fallback implementation for adapters like Memcached, APCu, Database, etc.
 * that don't have efficient tag-to-ID index capabilities like Redis or Filesystem.
 *
 * Uses namespace tag strategy for MATCHING_TAG mode:
 * - Generates composite tags for tag combinations
 * - Example: tags ['config', 'eav'] → saves with ['config', 'eav', 'NS_config|eav']
 * - MATCHING_TAG(['config', 'eav']) → invalidate 'NS_config|eav'
 *
 * Limitations:
 * - MATCHING_TAG only works if items were saved with those exact tags
 * - NOT_MATCHING_TAG is not efficiently supported (falls back to invalidating nothing)
 */
class Generic_Tag_Adapter implements Tag_Adapter_Interface
{
    private const NAMESPACE_PREFIX = 'NS_';
    private const NAMESPACE_SEPARATOR = '|';
    private const MAX_TAGS_FOR_NAMESPACE = 4;
    // Prevent combinatorial explosion
    /**
     * @var CacheItemPoolInterface
     */
    private Cache_Item_Pool_Interface $cache_pool;
    /**
     * @var bool
     */
    private bool $is_page_cache;
    /**
     * @param CacheItemPoolInterface $cachePool
     * @param bool $isPageCache Whether this is for page cache (FPC)
     */
    public function __construct(Cache_Item_Pool_Interface $cache_pool, bool $is_page_cache = false)
    {
        $this->cache_pool = $cache_pool;
        $this->is_page_cache = $is_page_cache;
    }
    /**
     * Generate namespace tag for a combination of tags
     *
     * @param array $tags
     * @return string
     */
    public function generate_namespace_tag(array $tags): string
    {
        $tags = array_values(array_unique($tags));
        sort($tags);
        // Consistent ordering
        return self::NAMESPACE_PREFIX . implode(self::NAMESPACE_SEPARATOR, $tags);
    }
    /**
     * Check if we should use namespace tags for this combination
     *
     * @param array $tags
     * @return bool
     */
    private function should_use_namespace_tags(array $tags): bool
    {
        $count = count($tags);
        // For page cache, use namespace tags for 2-4 tags
        if ($this->is_page_cache) {
            return $count >= 2 && $count <= self::MAX_TAGS_FOR_NAMESPACE;
        }
        // For application cache, don't use namespace tags
        return false;
    }
    /**
     * @inheritDoc
     *
     * Uses namespace tags for FPC, falls back to invalidating individual tags for application cache
     */
    public function get_ids_matching_tags(array $tags): array
    {
        // This method returns IDs, but we don't maintain explicit indices
        // Instead, we use it to determine what to invalidate
        // For generic adapters, we can't efficiently get IDs
        // This is handled in Symfony.php by using invalidateTags
        return [];
    }
    /**
     * @inheritDoc
     */
    public function get_ids_matching_any_tags(array $tags): array
    {
        // For generic adapters, we can't efficiently get IDs
        // This is handled in Symfony.php by using invalidateTags
        return [];
    }
    /**
     * @inheritDoc
     */
    public function get_ids_not_matching_tags(array $tags): array
    {
        // NOT_MATCHING_TAG is not efficiently supported for generic adapters
        return [];
    }
    /**
     * @inheritDoc
     */
    public function delete_by_ids(array $ids): bool
    {
        if (empty($ids)) {
            return true;
        }
        $success = $this->cache_pool->delete_items($ids);
        // Ensure changes are committed immediately (matches Zend behavior)
        if (method_exists($this->cache_pool, 'commit')) {
            $this->cache_pool->commit();
        }
        return $success;
    }
    /**
     * @inheritDoc
     *
     * For generic adapters, we don't maintain separate indices
     * Tags are stored directly with cache items by Symfony
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
    public function on_save(string $id, array $tags): void
    {
        // Intentional no-op: Tags are handled by Symfony's TagAwareAdapter
        // (for Database, APCu, and Memcached backends that lack native tag support)
    }
    // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
    public function on_remove(string $id): void
    {
        // Intentional no-op: No separate indices to update
    }
    // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
    /**
     * @inheritDoc
     */
    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
    public function clear_all_indices(): void
    {
        // Intentional no-op: No separate indices exist
    }
    // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
    /**
     * Get tags to save with cache item (including namespace tags if applicable)
     *
     * @param array $tags Original tags
     * @return array Tags including namespace tags if applicable
     */
    public function get_tags_for_save(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }
        // Start with original tags
        $all_tags = $tags;
        // Add namespace tag if applicable
        if ($this->should_use_namespace_tags($tags)) {
            $all_tags[] = $this->generate_namespace_tag($tags);
        }
        return array_values(array_unique($all_tags));
    }
    /**
     * Get tags to invalidate for MATCHING_TAG mode
     *
     * @param array $tags
     * @return array
     */
    public function get_tags_for_matching_tag(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }
        // Deduplicate and sort
        $unique_tags = array_values(array_unique($tags));
        // If we use namespace tags, invalidate the namespace tag
        if ($this->should_use_namespace_tags($unique_tags)) {
            sort($unique_tags);
            // Must match save() logic
            return [$this->generate_namespace_tag($unique_tags)];
        }
        // Otherwise, invalidate individual tags (OR logic, not perfect but best we can do)
        return $unique_tags;
    }
    /**
     * Check if this adapter should use namespace tags for MATCHING_TAG
     *
     * @return bool
     */
    public function uses_namespace_tags(): bool
    {
        return $this->is_page_cache;
    }
}