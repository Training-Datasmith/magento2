<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters;

use Psr\Cache\Cache_Item_Pool_Interface;
/**
 * Filesystem-specific tag adapter
 */
class Filesystem_Tag_Adapter implements Tag_Adapter_Interface
{
    /**
     * @var CacheItemPoolInterface
     */
    private Cache_Item_Pool_Interface $cache_pool;
    /**
     * @var string
     */
    private string $tag_directory;
    /**
     * @param CacheItemPoolInterface $cachePool
     * @param string $tagDirectory Directory to store tag index files
     */
    public function __construct(Cache_Item_Pool_Interface $cache_pool, string $tag_directory)
    {
        $this->cache_pool = $cache_pool;
        $this->tag_directory = rtrim($tag_directory, '/') . '/tags/';
        // Ensure tag directory exists with proper error handling
        if (!is_dir($this->tag_directory)) {
            if (!@mkdir($this->tag_directory, 0770, true) && !is_dir($this->tag_directory)) {
                throw new \RuntimeException(sprintf('Failed to create tag directory: %s', $this->tag_directory));
            }
        }
    }
    /**
     * Get tag file path
     *
     * @param string $tag
     * @return string
     */
    private function get_tag_file(string $tag): string
    {
        return $this->tag_directory . $tag;
    }
    /**
     * Read IDs from a tag file
     *
     * @param string $tag
     * @return array
     */
    private function get_tag_ids(string $tag): array
    {
        $file = $this->get_tag_file($tag);
        if (!file_exists($file)) {
            return [];
        }
        $content = @file_get_contents($file);
        if ($content === false || $content === '') {
            return [];
        }
        // IDs are stored one per line
        $ids = trim(substr($content, 0, strrpos($content, "\n") ?: strlen($content)));
        return $ids !== '' ? explode("\n", $ids) : [];
    }
    /**
     * Write IDs to a tag file
     *
     * @param string $tag
     * @param array $ids
     * @return void
     */
    private function set_tag_ids(string $tag, array $ids): void
    {
        $file = $this->get_tag_file($tag);
        if (empty($ids)) {
            // Remove tag file if no IDs
            @unlink($file);
            return;
        }
        // Ensure directory exists before writing (defensive check)
        if (!is_dir($this->tag_directory)) {
            @mkdir($this->tag_directory, 0770, true);
        }
        // Write IDs, one per line, with trailing newline
        $content = implode("\n", $ids) . "\n";
        if (@file_put_contents($file, $content, LOCK_EX) === false) {
            throw new \RuntimeException(sprintf('Failed to write tag file: %s', $file));
        }
    }
    /**
     * Add ID to a tag file
     *
     * @param string $tag
     * @param string $id
     * @return void
     */
    private function add_id_to_tag(string $tag, string $id): void
    {
        $ids = $this->get_tag_ids($tag);
        if (!in_array($id, $ids, true)) {
            $ids[] = $id;
            $this->set_tag_ids($tag, $ids);
        }
    }
    /**
     * Remove ID from a tag file
     *
     * @param string $tag
     * @param string $id
     * @return void
     */
    private function remove_id_from_tag(string $tag, string $id): void
    {
        $ids = $this->get_tag_ids($tag);
        $key = array_search($id, $ids, true);
        if ($key !== false) {
            unset($ids[$key]);
            $this->set_tag_ids($tag, array_values($ids));
        }
    }
    /**
     * @inheritDoc
     *
     * Uses array_intersect for true AND logic (similar to Colin Mollenhour's File backend)
     */
    public function get_ids_matching_tags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }
        // Get IDs for first tag
        $tag = array_shift($tags);
        $ids = $this->get_tag_ids($tag);
        // Intersect with remaining tags (AND logic)
        foreach ($tags as $tag) {
            if (empty($ids)) {
                break;
                // Early termination optimization
            }
            $ids = array_intersect($ids, $this->get_tag_ids($tag));
        }
        return array_values(array_unique($ids));
    }
    /**
     * @inheritDoc
     *
     * Uses array_merge for OR logic
     */
    public function get_ids_matching_any_tags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }
        $ids = [];
        foreach ($tags as $tag) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $ids = array_merge($ids, $this->get_tag_ids($tag));
        }
        return array_values(array_unique($ids));
    }
    /**
     * @inheritDoc
     *
     * Gets all IDs and removes those matching any of the given tags
     */
    public function get_ids_not_matching_tags(array $tags): array
    {
        if (empty($tags)) {
            // Return all IDs
            return $this->get_all_ids();
        }
        // Get all IDs
        $all_ids = $this->get_all_ids();
        // Get IDs matching any tag
        $matching_ids = $this->get_ids_matching_any_tags($tags);
        // Return difference
        return array_values(array_diff($all_ids, $matching_ids));
    }
    /**
     * Get all cache IDs from all tag files
     *
     * @return array
     */
    private function get_all_ids(): array
    {
        $all_ids = [];
        $tag_files = glob($this->tag_directory . '*');
        if ($tag_files === false) {
            return [];
        }
        foreach ($tag_files as $file) {
            if (is_file($file)) {
                $tag = basename($file);
                $ids = $this->get_tag_ids($tag);
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $all_ids = array_merge($all_ids, $ids);
            }
        }
        return array_values(array_unique($all_ids));
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
     * Maintains tag-to-ID indices in filesystem
     */
    public function on_save(string $id, array $tags): void
    {
        if (empty($tags)) {
            return;
        }
        // Add ID to each tag file
        foreach ($tags as $tag) {
            $this->add_id_to_tag($tag, $id);
        }
    }
    /**
     * @inheritDoc
     *
     * Removes ID from all tag files
     */
    public function on_remove(string $id): void
    {
        // We need to scan all tag files and remove this ID
        $tag_files = glob($this->tag_directory . '*');
        if ($tag_files === false) {
            return;
        }
        foreach ($tag_files as $file) {
            if (is_file($file)) {
                $tag = basename($file);
                $this->remove_id_from_tag($tag, $id);
            }
        }
    }
    /**
     * @inheritDoc
     */
    public function clear_all_indices(): void
    {
        // Remove all tag files
        $tag_files = glob($this->tag_directory . '*');
        if ($tag_files === false) {
            return;
        }
        foreach ($tag_files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}