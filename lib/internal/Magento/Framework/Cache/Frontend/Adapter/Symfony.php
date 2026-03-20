<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter;

use Closure;
use InvalidArgumentException;
use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters\Generic_Tag_Adapter;
use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters\Tag_Adapter_Interface;
use Magento\Framework\Cache\Frontend_Interface;
use Psr\Cache\Cache_Item_Interface;
use Psr\Cache\Cache_Item_Pool_Interface;
use Symfony\Component\Cache\Adapter\Tag_Aware_Adapter_Interface;
use Symfony\Component\Cache\Cache_Item;
/**
 * Symfony Cache adapter for Magento
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Symfony implements Frontend_Interface
{
    public const DEFAULT_CACHE_PREFIX = '69d_';
    public const DEFAULT_LIFETIME = 7200;
    public const FALLBACK_EXPIRY = 86400;
    public const ASSUMED_LIFETIME = 7200;
    /**
     * @var CacheItemPoolInterface
     */
    private Cache_Item_Pool_Interface $cache;
    /**
     * @var TagAdapterInterface
     */
    private Tag_Adapter_Interface $adapter;
    /**
     * @var Closure|null
     */
    private ?Closure $cache_factory;
    /**
     * @var int
     */
    private int $pid;
    /**
     * @var array
     */
    private array $parent_cache_pools = [];
    /**
     * @var bool|null
     */
    private ?bool $is_tag_aware = null;
    /**
     * @var int
     */
    private int $default_lifetime;
    /**
     * @var string
     */
    private string $id_prefix;
    /**
     * @var bool
     */
    private bool $batch_mode = false;
    /**
     * @var array
     */
    private array $batched_items = [];
    /**
     * @var bool
     */
    private bool $always_defer_saves = false;
    /**
     * @var bool
     */
    private bool $has_pending_writes = false;
    /**
     * @var array
     */
    private array $response_cache = [];
    /**
     * @var int
     */
    private const RESPONSE_CACHE_MAX_SIZE = 500;
    /**
     * @var int Response cache TTL in seconds
     *
     * Set to 0 to disable (safer for multi-instance scenarios)
     * Can be increased in single-instance production environments
     */
    private const RESPONSE_CACHE_TTL = 0;
    /**
     * Constructor
     *
     * @param Closure $cacheFactory Factory that creates the cache pool
     * @param TagAdapterInterface|null $adapter Backend-specific tag adapter
     * @param int $defaultLifetime Default cache lifetime in seconds
     * @param string $idPrefix Cache ID prefix
     */
    public function __construct(Closure $cache_factory, ?Tag_Adapter_Interface $adapter = null, int $default_lifetime = self::DEFAULT_LIFETIME, string $id_prefix = self::DEFAULT_CACHE_PREFIX)
    {
        $this->cache_factory = $cache_factory;
        $this->pid = getmypid();
        $this->cache = $cache_factory();
        $this->default_lifetime = $default_lifetime;
        $this->id_prefix = $id_prefix;
        $this->adapter = $adapter ?? new Generic_Tag_Adapter($this->cache);
    }
    /**
     * Get cache pool instance (with process ID check)
     *
     * @return CacheItemPoolInterface
     */
    private function get_cache(): Cache_Item_Pool_Interface
    {
        $current_pid = getmypid();
        if ($current_pid !== $this->pid) {
            $this->parent_cache_pools[] = $this->cache;
            $this->cache = ($this->cache_factory)();
            $this->pid = $current_pid;
            $this->is_tag_aware = null;
        }
        return $this->cache;
    }
    /**
     * Check if cache supports tag-aware operations
     *
     * @return bool
     */
    private function is_tag_aware(): bool
    {
        if ($this->is_tag_aware === null) {
            $this->is_tag_aware = $this->get_cache() instanceof Tag_Aware_Adapter_Interface;
        }
        return $this->is_tag_aware;
    }
    /**
     * Clean and normalize cache identifier
     *
     * @param string|null $identifier
     * @return string|null
     */
    private function clean_identifier(?string $identifier): ?string
    {
        if ($identifier === null) {
            return null;
        }
        $identifier = strtoupper($identifier);
        $cleaned = str_replace('.', '__', $identifier);
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $cleaned);
    }
    /**
     * Clean multiple cache identifiers
     *
     * @param array $identifiers
     * @return array
     */
    private function clean_identifiers(array $identifiers): array
    {
        return array_map([$this, 'cleanIdentifier'], $identifiers);
    }
    /**
     * @inheritDoc
     */
    public function test($identifier)
    {
        $clean_id = $this->clean_identifier($identifier);
        $cache_key = 'test:' . $clean_id;
        // OPTIMIZATION: Check response cache first (Predis optimization)
        if (isset($this->response_cache[$cache_key])) {
            $cached = $this->response_cache[$cache_key];
            if (time() - $cached['time'] < self::RESPONSE_CACHE_TTL) {
                return $cached['result'];
            }
            unset($this->response_cache[$cache_key]);
        }
        if ($this->has_pending_writes) {
            $this->commit_pending_writes();
        }
        $cache = $this->get_cache();
        $item = $cache->get_item($clean_id);
        if (!$item->is_hit()) {
            return false;
        }
        $value = $item->get();
        $result = is_array($value) && isset($value['mtime']) ? (int) $value['mtime'] : time();
        // Cache result in memory
        if (count($this->response_cache) < self::RESPONSE_CACHE_MAX_SIZE) {
            $this->response_cache[$cache_key] = ['result' => $result, 'time' => time()];
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        $clean_id = $this->clean_identifier($identifier);
        $cache_key = 'load:' . $clean_id;
        // OPTIMIZATION: Check response cache first (Predis optimization)
        if (isset($this->response_cache[$cache_key])) {
            $cached = $this->response_cache[$cache_key];
            if (time() - $cached['time'] < self::RESPONSE_CACHE_TTL) {
                return $cached['result'];
            }
            unset($this->response_cache[$cache_key]);
        }
        if ($this->has_pending_writes) {
            $this->commit_pending_writes();
        }
        $cache = $this->get_cache();
        $item = $cache->get_item($clean_id);
        if (!$item->is_hit()) {
            return false;
        }
        $wrapped_data = $item->get();
        $result = is_array($wrapped_data) && array_key_exists('data', $wrapped_data) ? $wrapped_data['data'] : $wrapped_data;
        // Cache result in memory (only cache hits, not misses)
        if ($result !== false && count($this->response_cache) < self::RESPONSE_CACHE_MAX_SIZE) {
            $this->response_cache[$cache_key] = ['result' => $result, 'time' => time()];
        }
        return $result;
    }
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($data, $identifier, array $tags = [], $life_time = null)
    {
        $cache = $this->get_cache();
        $clean_id = $this->clean_identifier($identifier);
        // Clear response cache for this key (important for concurrent access)
        unset($this->response_cache['test:' . $clean_id]);
        unset($this->response_cache['load:' . $clean_id]);
        $item = $cache->get_item($clean_id);
        // Calculate actual lifetime to use
        $actual_lifetime = $this->calculate_actual_lifetime($life_time);
        // Clean tags once for reuse
        $clean_tags = !empty($tags) ? $this->clean_identifiers($tags) : [];
        // OPTIMIZATION: Conditional metadata wrapping
        $needs_metadata = !empty($clean_tags) || $actual_lifetime !== $this->default_lifetime;
        if ($needs_metadata) {
            $this->prepare_item_with_metadata($item, $data, $clean_tags, $actual_lifetime);
        } else {
            // FAST PATH: Store data directly without metadata wrapper
            $item->set($data);
        }
        // Set expiration on Symfony item
        if ($actual_lifetime !== null) {
            $item->expires_after($actual_lifetime);
        }
        // AUTOMATIC BATCHING: Always defer saves for performance
        // Commits happen automatically before reads and at request end
        if ($this->always_defer_saves) {
            $this->batched_items[$clean_id] = ['item' => $item, 'tags' => $clean_tags];
            $this->has_pending_writes = true;
            return $cache->save_deferred($item);
        }
        // LEGACY MODE: Immediate save (only if alwaysDeferSaves is disabled)
        // BATCH MODE: Defer save if batching is enabled
        if ($this->batch_mode) {
            $this->batched_items[$clean_id] = ['item' => $item, 'tags' => $clean_tags];
            $this->has_pending_writes = true;
            return $cache->save_deferred($item);
        }
        // NORMAL MODE: Immediate save
        $success = $cache->save($item);
        // Commit and notify helpers
        $this->commit_and_notify($cache, $success, $clean_id, $clean_tags);
        return $success;
    }
    /**
     * Destructor - ensures any pending writes are committed
     *
     * This provides automatic batching: all saves are deferred during the request,
     * and committed once at the end. This reduces overhead from N×commit to 1×commit.
     *
     * @return void
     */
    public function __destruct()
    {
        // Auto-commit any pending writes
        if ($this->has_pending_writes) {
            try {
                $this->commit_pending_writes();
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (\Exception $e) {
                // Intentional no-op: Silently fail in destructor (request is ending anyway)
                // In production, this would be logged
            }
        }
    }
    /**
     * Commit all pending deferred writes
     *
     * This method is called automatically:
     * - Before any read (load/test operations)
     * - At end of request (__destruct)
     * - When explicit commit is requested (endBatch)
     *
     * @return bool True if commit was successful
     */
    private function commit_pending_writes(): bool
    {
        if (!$this->has_pending_writes || empty($this->batched_items)) {
            return true;
        }
        $cache = $this->get_cache();
        // Commit all deferred items
        $success = $cache->commit();
        // Notify tag adapters about all saved items
        foreach ($this->batched_items as $clean_id => $item_data) {
            $this->commit_and_notify($cache, $success, $clean_id, $item_data['tags']);
        }
        // Clear state
        $this->batched_items = [];
        $this->has_pending_writes = false;
        return $success;
    }
    /**
     * Begin batch mode for cache operations
     *
     * When in batch mode, all save() calls will be deferred until endBatch() is called.
     * This reduces overhead from 79 × commit() to 1 × commit() for bulk operations.
     *
     * Performance impact:
     * - Without batching: 79 saves × 0.8ms = 63ms
     * - With batching: 1 commit × 0.8ms = 0.8ms
     * - Savings: ~62ms per bulk operation
     *
     * Usage:
     * <code>
     * $cache->beginBatch();
     * foreach ($items as $item) {
     *     $cache->save($data, $id, $tags);  // Deferred
     * }
     * $cache->endBatch();  // Commit all at once
     * </code>
     *
     * Note: If endBatch() is not called, items will be auto-committed
     * in __destruct() at the end of the request.
     *
     * @return void
     */
    public function begin_batch(): void
    {
        $this->batch_mode = true;
        $this->always_defer_saves = true;
        // Enable automatic deferring
        $this->batched_items = [];
    }
    /**
     * End batch mode and commit all deferred cache operations
     *
     * Note: With alwaysDeferSaves mode, this is optional since commits
     * happen automatically before reads and at request end.
     *
     * @return bool True if all items were committed successfully
     */
    public function end_batch(): bool
    {
        $this->batch_mode = false;
        $this->always_defer_saves = false;
        // Disable automatic deferring
        return $this->commit_pending_writes();
    }
    /**
     * Calculate the actual lifetime to use for cache entry
     *
     * Enforces Redis MAX_LIFETIME limit (30 days) to prevent TTL overflow issues.
     * Matches Zend's Cm_Cache_Backend_Redis behavior.
     *
     * @param mixed $lifeTime
     * @return int|null
     */
    private function calculate_actual_lifetime($life_time): ?int
    {
        $actual_lifetime = null;
        if ($life_time !== null && $life_time !== false && $life_time !== 0) {
            $actual_lifetime = (int) $life_time;
        } elseif ($life_time === 0 || $life_time === false) {
            // 0 or false means use default in Zend behavior
            $actual_lifetime = $this->default_lifetime;
        } else {
            $actual_lifetime = $this->default_lifetime;
        }
        // Enforce Redis MAX_LIFETIME limit (matches Zend behavior)
        // Beyond 30 days, Redis may have TTL tracking issues
        if ($actual_lifetime !== null && $actual_lifetime > Symfony_Adapter_Provider::REDIS_MAX_LIFETIME) {
            $actual_lifetime = Symfony_Adapter_Provider::REDIS_MAX_LIFETIME;
        }
        return $actual_lifetime;
    }
    /**
     * Prepare cache item with metadata wrapper
     *
     * @param CacheItemInterface $item
     * @param mixed $data
     * @param array $cleanTags
     * @param int|null $actualLifetime
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function prepare_item_with_metadata($item, $data, array $clean_tags, ?int $actual_lifetime): void
    {
        $now = time();
        // Get enhanced tags (including namespace tags if applicable)
        $tags_to_set = $clean_tags;
        if ($this->adapter instanceof Generic_Tag_Adapter && !empty($clean_tags)) {
            $tags_to_set = $this->adapter->get_tags_for_save($clean_tags);
        }
        // Calculate expiry timestamp (for Zend compatibility)
        $expiry = $actual_lifetime !== null ? $now + $actual_lifetime : null;
        // Wrap data with metadata for consistent timestamps
        $wrapped_data = ['data' => $data, 'mtime' => $now, 'expire' => $expiry, 'tags' => array_values(array_unique($tags_to_set))];
        $item->set($wrapped_data);
        // Handle tags
        if ($this->is_tag_aware() && !empty($tags_to_set)) {
            $item->tag($tags_to_set);
        }
    }
    /**
     * Commit cache and notify helpers
     *
     * @param CacheItemPoolInterface $cache
     * @param bool $success
     * @param string $cleanId
     * @param array $cleanTags
     * @return void
     */
    private function commit_and_notify($cache, bool $success, string $clean_id, array $clean_tags): void
    {
        // Ensure immediate persistence (commit any deferred saves)
        if ($success && method_exists($cache, 'commit')) {
            $cache->commit();
        }
        // Notify helper about the save (for Redis/Filesystem to maintain indices)
        // Note: onSave() already handles reverse index, no need for separate call
        if ($success && !empty($clean_tags)) {
            $this->adapter->on_save($clean_id, $clean_tags);
        }
    }
    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        if ($this->has_pending_writes) {
            $this->commit_pending_writes();
        }
        $cache = $this->get_cache();
        $clean_id = $this->clean_identifier($identifier);
        // Clear from response cache
        unset($this->response_cache['test:' . $clean_id]);
        unset($this->response_cache['load:' . $clean_id]);
        $this->adapter->on_remove($clean_id);
        $success = $cache->delete_item($clean_id);
        if (method_exists($cache, 'commit')) {
            $cache->commit();
        }
        return $success;
    }
    /**
     * @inheritDoc
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, array $tags = [])
    {
        $this->response_cache = [];
        if ($this->has_pending_writes) {
            $this->commit_pending_writes();
        }
        // Validate cleaning mode
        $valid_modes = [Cache_Constants::CLEANING_MODE_ALL, Cache_Constants::CLEANING_MODE_OLD, Cache_Constants::CLEANING_MODE_MATCHING_TAG, Cache_Constants::CLEANING_MODE_NOT_MATCHING_TAG, Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG];
        if (!in_array($mode, $valid_modes, true)) {
            throw new InvalidArgumentException("Invalid cleaning mode '{$mode}'. Supported modes: " . 'ALL, OLD, MATCHING_TAG, NOT_MATCHING_TAG, MATCHING_ANY_TAG');
        }
        $cache = $this->get_cache();
        return match ($mode) {
            Cache_Constants::CLEANING_MODE_ALL, 'all' => $this->clean_all($cache),
            Cache_Constants::CLEANING_MODE_OLD, 'old' => $this->clean_old($cache),
            Cache_Constants::CLEANING_MODE_MATCHING_TAG, 'matchingTag' => $this->clean_matching_tag($cache, $tags),
            Cache_Constants::CLEANING_MODE_NOT_MATCHING_TAG, 'notMatchingTag' => $this->clean_not_matching_tag($cache, $tags),
            Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG, 'matchingAnyTag' => $this->clean_matching_any_tag($cache, $tags),
            default => throw new InvalidArgumentException("Unsupported cleaning mode: {$mode}"),
        };
    }
    /**
     * Clean all cache entries
     *
     * @param CacheItemPoolInterface $cache
     * @return bool
     */
    private function clean_all(Cache_Item_Pool_Interface $cache): bool
    {
        $this->response_cache = [];
        $this->adapter->clear_all_indices();
        $success = $cache->clear();
        if (method_exists($cache, 'commit')) {
            $cache->commit();
        }
        return $success;
    }
    /**
     * Clean old/expired cache entries
     *
     * @param CacheItemPoolInterface $cache
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function clean_old(Cache_Item_Pool_Interface $cache): bool
    {
        // Symfony handles expiration automatically
        // This is a no-op as expired items are not returned
        return true;
    }
    /**
     * Clean entries matching ALL given tags (AND logic)
     *
     * @param CacheItemPoolInterface $cache
     * @param array $tags
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function clean_matching_tag(Cache_Item_Pool_Interface $cache, array $tags): bool
    {
        if (empty($tags)) {
            return true;
        }
        $clean_tags = $this->clean_identifiers($tags);
        // For GenericHelper with namespace tags, use the namespace tag
        if ($this->adapter instanceof Generic_Tag_Adapter) {
            if ($this->adapter->uses_namespace_tags()) {
                $tags_to_invalidate = $this->adapter->get_tags_for_matching_tag($clean_tags);
                if ($this->is_tag_aware()) {
                    $success = $cache->invalidate_tags($tags_to_invalidate);
                    if (method_exists($cache, 'commit')) {
                        $cache->commit();
                    }
                    return $success;
                }
                return false;
            } else {
                // For non-FPC generic adapters, use OR logic (best we can do)
                if ($this->is_tag_aware()) {
                    $success = $cache->invalidate_tags($clean_tags);
                    if (method_exists($cache, 'commit')) {
                        $cache->commit();
                    }
                    return $success;
                }
                return false;
            }
        }
        // For Redis/Filesystem helpers with native AND support
        $ids = $this->adapter->get_ids_matching_tags($clean_tags);
        if (empty($ids)) {
            return true;
        }
        return $this->adapter->delete_by_ids($ids);
    }
    /**
     * Clean entries NOT matching any of the given tags
     *
     * @param CacheItemPoolInterface $cache
     * @param array $tags
     * @return bool
     */
    private function clean_not_matching_tag(Cache_Item_Pool_Interface $cache, array $tags): bool
    {
        if (empty($tags)) {
            // No tags means clean all
            return $this->clean_all($cache);
        }
        $clean_tags = $this->clean_identifiers($tags);
        $ids = $this->adapter->get_ids_not_matching_tags($clean_tags);
        if (empty($ids)) {
            return true;
        }
        return $this->adapter->delete_by_ids($ids);
    }
    /**
     * Get cache entry metadata (Zend compatibility)
     *
     * @param string $id
     * @return array|false
     */
    public function get_metadatas($id)
    {
        $cache = $this->get_cache();
        $clean_id = $this->clean_identifier($id);
        $item = $cache->get_item($clean_id);
        if (!$item->is_hit()) {
            return false;
        }
        $wrapped_data = $item->get();
        // Return stored metadata from wrapper
        if (is_array($wrapped_data) && isset($wrapped_data['mtime'])) {
            // Add cache ID prefix to tags (to match Zend behavior)
            $stored_tags = $wrapped_data['tags'] ?? [];
            $tags = array_values(array_map(function ($tag) {
                return self::DEFAULT_CACHE_PREFIX . $tag;
            }, $stored_tags));
            return ['expire' => $wrapped_data['expire'] ?? null, 'tags' => $tags, 'mtime' => $wrapped_data['mtime']];
        }
        // Fallback for non-wrapped data (shouldn't happen)
        // Calculate metadata from Symfony metadata
        $metadata = $item->get_metadata();
        // Get expiry timestamp from Symfony metadata
        $expiry = null;
        if (isset($metadata[Cache_Item::METADATA_EXPIRY])) {
            $expiry = (int) $metadata[Cache_Item::METADATA_EXPIRY];
        }
        // If no expiry, default to now + 24 hours
        if (!$expiry) {
            $expiry = time() + self::FALLBACK_EXPIRY;
        }
        // Calculate mtime from expiry (Symfony doesn't store creation time)
        // Use ASSUMED_LIFETIME for approximation: mtime ≈ expiry - lifetime
        $mtime = $expiry - self::ASSUMED_LIFETIME;
        // Ensure mtime is not in the future
        $now = time();
        if ($mtime > $now) {
            $mtime = $now;
        }
        // Get tags from metadata and add cache ID prefix
        $tags = [];
        if (isset($metadata[Cache_Item::METADATA_TAGS])) {
            $raw_tags = $metadata[Cache_Item::METADATA_TAGS];
            $tags = array_values(array_map(function ($tag) {
                return self::DEFAULT_CACHE_PREFIX . $tag;
            }, $raw_tags));
        }
        return ['expire' => $expiry, 'tags' => $tags, 'mtime' => $mtime];
    }
    /**
     * Clean entries matching ANY of the given tags (OR logic)
     *
     * OPTIMIZED: Prefer adapter-based batch processing over TagAwareAdapter
     * for better performance with configurable products (3-5× faster)
     *
     * @param CacheItemPoolInterface $cache
     * @param array $tags
     * @return bool
     */
    private function clean_matching_any_tag(Cache_Item_Pool_Interface $cache, array $tags): bool
    {
        if (empty($tags)) {
            return true;
        }
        $clean_tags = $this->clean_identifiers($tags);
        // OPTIMIZATION: Use adapter for batch tag processing (faster than TagAwareAdapter)
        // The adapter uses a single Redis SUNION command to get all IDs for all tags,
        // then batch-deletes them. This is 3-5× faster than TagAwareAdapter's approach.
        if ($this->adapter && !empty($clean_tags)) {
            $ids = $this->adapter->get_ids_matching_any_tags($clean_tags);
            if (empty($ids)) {
                return true;
            }
            // Batch delete all IDs at once
            return $this->adapter->delete_by_ids($ids);
        }
        // Fallback: Try Symfony's native invalidateTags (OR logic)
        // This path is used only if adapter is not available
        if ($this->is_tag_aware()) {
            // Note: commit() is called internally by invalidateTags, no need to call explicitly
            return $cache->invalidate_tags($clean_tags);
        }
        // Last resort: iterate tags (should rarely happen)
        $success = true;
        foreach ($clean_tags as $tag) {
            if (!$cache->clear($tag)) {
                $success = false;
            }
        }
        // Ensure changes are committed immediately (matches Zend behavior)
        if (method_exists($cache, 'commit')) {
            $cache->commit();
        }
        return $success;
    }
    /**
     * @inheritDoc
     */
    public function get_backend()
    {
        return new Symfony\Backend_Wrapper($this->get_cache(), $this->adapter, $this);
    }
    /**
     * @inheritDoc
     */
    public function get_low_level_frontend()
    {
        return new Symfony\Low_Level_Frontend($this->get_cache(), $this, $this->adapter, $this->id_prefix, $this->default_lifetime);
    }
    /**
     * @inheritDoc
     */
    public function get_frontend()
    {
        return $this;
    }
}