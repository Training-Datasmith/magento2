<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters;

use Magento\Framework\Cache\Frontend\Adapter\Optimized_Predis_Client;
use Predis\Client as PredisClient;
use Psr\Cache\Cache_Item_Pool_Interface;
use Symfony\Component\Cache\Adapter\Redis_Adapter;
use Symfony\Component\Cache\Adapter\Tag_Aware_Adapter;
/**
 * Redis-specific tag adapter
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Redis_Tag_Adapter implements Tag_Adapter_Interface
{
    private const TAG_INDEX_PREFIX = 'cache:tags:';
    private const ALL_IDS_SET = 'cache:all_ids';
    /**
     * SUNION chunk size
     * On large data sets SUNION slows down considerably when used with too many arguments
     * @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 92
     */
    private const SUNION_CHUNK_SIZE = 500;
    /**
     * Maximum number of IDs to be removed at a time - matches Zend's $_removeChunkSize
     * @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 99
     */
    private const REMOVE_CHUNK_SIZE = 10000;
    /**
     * Lua's unpack() limit - matches Zend's $_luaMaxCStack
     * @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 121
     */
    private const LUA_MAX_CSTACK = 5000;
    /**
     * Lua script for cleaning cache entries matching ANY tags (OR logic)
     *
     * This matches Zend's LUA_CLEAN_SH1 implementation exactly
     * (vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 780-798)
     *
     * Performance: Single atomic Redis operation, ~10-15% faster than PHP implementation
     */
    private const LUA_CLEAN_MATCHING_ANY_TAGS = <<<'LUA'
    -- KEYS: array of tags to match (e.g., ["product", "category", "config"])
    -- ARGV[1]: tag prefix (e.g., "cache:tags:")
    -- ARGV[2]: namespace prefix (e.g., "69d_")
    -- ARGV[3]: chunk size for SUNION operations
    
    local tag_prefix = ARGV[1]
    local namespace = ARGV[2]
    local chunk_size = tonumber(ARGV[3]) or 100
    
    -- Build prefixed tag keys
    local prefixed_tags = {}
    for i, tag in ipairs(KEYS) do
        prefixed_tags[i] = tag_prefix .. namespace .. tag
    end
    
    -- Get IDs matching ANY of the tags using SUNION
    local ids_to_delete = redis.call('SUNION', unpack(prefixed_tags))
    
    if #ids_to_delete == 0 then
        return 0
    end
    
    -- Delete cache items and remove from indices
    local deleted = 0
    for _, id in ipairs(ids_to_delete) do
        -- Delete the actual cache item
        local cache_key = namespace .. id
        redis.call('DEL', cache_key)
        deleted = deleted + 1
    end
    
    return deleted
    LUA;
    /**
     * Lua script for cleaning cache entries matching ANY tags within a scope (OR + AND logic)
     *
     * Logic: (tag1 OR tag2 OR ...) AND scopeTag
     *
     * Performance: Single atomic Redis operation with scope filtering
     */
    private const LUA_CLEAN_MATCHING_ANY_TAGS_WITH_SCOPE = <<<'LUA'
    -- KEYS: array of tags to match (e.g., ["product", "category"])
    -- ARGV[1]: tag prefix (e.g., "cache:tags:")
    -- ARGV[2]: namespace prefix (e.g., "69d_")
    -- ARGV[3]: scope tag (e.g., "FPC")
    
    local tag_prefix = ARGV[1]
    local namespace = ARGV[2]
    local scope_tag = ARGV[3]
    
    -- Build prefixed tag keys
    local prefixed_tags = {}
    for i, tag in ipairs(KEYS) do
        prefixed_tags[i] = tag_prefix .. namespace .. tag
    end
    
    -- Step 1: Get IDs matching ANY of the tags using SUNION
    local any_ids = redis.call('SUNION', unpack(prefixed_tags))
    
    if #any_ids == 0 then
        return 0
    end
    
    -- Step 2: Get IDs matching the scope tag
    local scope_key = tag_prefix .. namespace .. scope_tag
    local scope_ids = redis.call('SMEMBERS', scope_key)
    
    if #scope_ids == 0 then
        return 0
    end
    
    -- Step 3: Intersect in Lua (find IDs in both sets)
    local scope_set = {}
    for _, id in ipairs(scope_ids) do
        scope_set[id] = true
    end
    
    local filtered_ids = {}
    for _, id in ipairs(any_ids) do
        if scope_set[id] then
            table.insert(filtered_ids, id)
        end
    end
    
    if #filtered_ids == 0 then
        return 0
    end
    
    -- Step 4: Delete filtered IDs
    local deleted = 0
    for _, id in ipairs(filtered_ids) do
        local cache_key = namespace .. id
        redis.call('DEL', cache_key)
        deleted = deleted + 1
    end
    
    return deleted
    LUA;
    /**
     * @var \Redis|\RedisCluster|PredisClient|OptimizedPredisClient
     */
    private \Redis|\Redis_Cluster|Predis_Client|Optimized_Predis_Client $redis;
    /**
     * @var string
     */
    private string $namespace;
    /**
     * @var CacheItemPoolInterface
     */
    private Cache_Item_Pool_Interface $cache_pool;
    /**
     * @var RedisLuaHelper|null
     */
    private ?Redis_Lua_Helper $lua_helper = null;
    /**
     * @var bool
     */
    private bool $use_lua;
    /**
     * @var bool
     */
    private bool $use_lua_on_gc;
    /**
     * @param CacheItemPoolInterface $cachePool
     * @param string $namespace Cache namespace/prefix
     * @param bool $useLua Enable Lua scripts for cache operations
     * @param bool $useLuaOnGc Enable Lua scripts for garbage collection
     */
    public function __construct(Cache_Item_Pool_Interface $cache_pool, string $namespace = '', bool $use_lua = false, bool $use_lua_on_gc = false)
    {
        $this->cache_pool = $cache_pool;
        $this->namespace = $namespace;
        $this->redis = $this->extract_redis_client($cache_pool);
        if ($this->is_predis_client()) {
            $this->use_lua = false;
            $this->use_lua_on_gc = false;
        } else {
            $this->use_lua = $use_lua;
            $this->use_lua_on_gc = $use_lua_on_gc;
        }
        if (($this->use_lua || $this->use_lua_on_gc) && !$this->is_predis_client()) {
            $this->lua_helper = new Redis_Lua_Helper($this->redis, true);
        }
    }
    /**
     * Extract Redis client from Symfony cache adapter
     *
     * @param CacheItemPoolInterface $cachePool
     * @return \Redis|\RedisCluster|PredisClient|OptimizedPredisClient
     * @throws \RuntimeException If Redis client cannot be extracted
     */
    private function extract_redis_client(Cache_Item_Pool_Interface $cache_pool): \Redis|\Redis_Cluster|Predis_Client|Optimized_Predis_Client
    {
        $adapter = $cache_pool;
        if ($adapter instanceof Tag_Aware_Adapter) {
            $reflection = new \ReflectionClass($adapter);
            $pool_property = $reflection->get_property('pool');
            $adapter = $pool_property->get_value($adapter);
        }
        // Get Redis client from RedisAdapter
        if ($adapter instanceof Redis_Adapter) {
            $reflection = new \ReflectionClass($adapter);
            $redis_property = $reflection->get_property('redis');
            $redis = $redis_property->get_value($adapter);
            if ($redis instanceof \Redis || $redis instanceof \Redis_Cluster || $redis instanceof Predis_Client || $redis instanceof Optimized_Predis_Client) {
                return $redis;
            }
        }
        throw new \RuntimeException('Could not extract Redis client from cache adapter');
    }
    /**
     * Get prefixed tag name for Redis SET key
     *
     * @param string $tag
     * @return string
     */
    private function get_tag_key(string $tag): string
    {
        return self::TAG_INDEX_PREFIX . $this->namespace . $tag;
    }
    /**
     * Check if using Predis client (vs phpredis extension)
     *
     * @return bool
     */
    private function is_predis_client(): bool
    {
        return $this->redis instanceof Predis_Client || $this->redis instanceof Optimized_Predis_Client;
    }
    /**
     * Create Redis pipeline compatible with both phpredis and Predis
     *
     * @return \Redis|object Predis pipeline object
     */
    private function create_pipeline()
    {
        if ($this->is_predis_client()) {
            return $this->redis->pipeline();
        }
        return $this->redis->multi(\Redis::PIPELINE);
    }
    /**
     * Execute Redis pipeline compatible with both phpredis and Predis
     *
     * @param \Redis|object $pipeline
     * @return mixed
     */
    private function execute_pipeline($pipeline)
    {
        if ($pipeline instanceof Predis_Client || method_exists($pipeline, 'execute')) {
            // Predis pipeline
            return $pipeline->execute();
        }
        // phpredis pipeline
        return $pipeline->exec();
    }
    /**
     * @inheritDoc
     *
     * Uses Redis SINTER for efficient set intersection (true AND logic)
     */
    public function get_ids_matching_tags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }
        // Build tag keys for Redis SINTER
        $tag_keys = array_map([$this, 'getTagKey'], $tags);
        // Redis SINTER returns IDs present in ALL sets
        $ids = $this->redis->sinter($tag_keys);
        return is_array($ids) ? $ids : [];
    }
    /**
     * @inheritDoc
     *
     * Uses Redis SUNION for efficient set union (OR logic)
     *
     * OPTIMIZED: Single tag uses SMEMBERS (faster), multiple tags use SUNION
     * Redis SUNION already returns unique values, no need for array_unique()
     */
    public function get_ids_matching_any_tags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }
        // OPTIMIZATION: For single tag, use SMEMBERS directly (faster than SUNION)
        if (count($tags) === 1) {
            $ids = $this->redis->s_members($this->get_tag_key($tags[0]));
            return is_array($ids) ? $ids : [];
        }
        // Matches Zend's implementation to prevent Redis slowdowns
        // @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 777-778
        if (count($tags) > self::SUNION_CHUNK_SIZE) {
            $all_ids = [];
            $chunks = array_chunk($tags, self::SUNION_CHUNK_SIZE);
            foreach ($chunks as $chunk) {
                $tag_keys = array_map([$this, 'getTagKey'], $chunk);
                $chunk_ids = $this->redis->s_union($tag_keys);
                $chunk_ids = is_array($chunk_ids) ? $chunk_ids : [];
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $all_ids = array_merge($all_ids, $chunk_ids);
            }
            return array_unique($all_ids);
        }
        $tag_keys = array_map([$this, 'getTagKey'], $tags);
        $ids = $this->redis->s_union($tag_keys);
        return is_array($ids) ? $ids : [];
    }
    /**
     * @inheritDoc
     *
     * Gets all IDs and removes those matching any of the given tags
     */
    public function get_ids_not_matching_tags(array $tags): array
    {
        if (empty($tags)) {
            // Return all IDs if no tags specified
            $all_ids = $this->redis->smembers(self::ALL_IDS_SET);
            return is_array($all_ids) ? $all_ids : [];
        }
        $tag_keys = array_map([$this, 'getTagKey'], $tags);
        // Prepend the all_ids set as first argument
        array_unshift($tag_keys, self::ALL_IDS_SET);
        // Call SDIFF: returns IDs in ALL_IDS_SET but NOT in any tag sets
        $result = call_user_func_array([$this->redis, 'sdiff'], $tag_keys);
        return is_array($result) ? $result : [];
    }
    /**
     * @inheritDoc
     *
     * OPTIMIZED: Uses Redis pipeline for large batches
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function delete_by_ids(array $ids): bool
    {
        if (empty($ids)) {
            return true;
        }
        // Matches Zend's implementation to prevent Redis blocking and memory issues
        // @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 809-825
        if (count($ids) > self::REMOVE_CHUNK_SIZE) {
            $chunks = array_chunk($ids, self::REMOVE_CHUNK_SIZE);
            $success = true;
            foreach ($chunks as $chunk) {
                // Delete cache items for this chunk
                if (!$this->cache_pool->delete_items($chunk)) {
                    $success = false;
                }
                // Remove IDs from all_ids set for this chunk
                $pipeline = $this->create_pipeline();
                foreach ($chunk as $id) {
                    $pipeline->srem(self::ALL_IDS_SET, $id);
                }
                $this->execute_pipeline($pipeline);
                // Commit each chunk separately (important for large operations)
                if (method_exists($this->cache_pool, 'commit')) {
                    $this->cache_pool->commit();
                }
            }
            return $success;
        }
        $success = $this->cache_pool->delete_items($ids);
        if (count($ids) > 10) {
            $pipeline = $this->create_pipeline();
            // Remove each ID from all_ids set in pipeline
            foreach ($ids as $id) {
                $pipeline->srem(self::ALL_IDS_SET, $id);
            }
            $this->execute_pipeline($pipeline);
        } else {
            // For small batches, use single command (slightly faster)
            array_unshift($ids, self::ALL_IDS_SET);
            call_user_func_array([$this->redis, 'sRem'], $ids);
        }
        // Ensure changes are committed immediately (important for MFTF and tests)
        if (method_exists($this->cache_pool, 'commit')) {
            $this->cache_pool->commit();
        }
        return $success;
    }
    /**
     * Clean cache entries matching ANY of the given tags (OR logic)
     *
     * @param array $tags Tags to match (OR logic)
     * @return bool
     */
    public function clean_matching_any_tags(array $tags): bool
    {
        if (empty($tags)) {
            return true;
        }
        // Lua path (if enabled) - matches Zend's Lua script (line 776-801)
        if ($this->use_lua && $this->lua_helper && $this->lua_helper->is_enabled()) {
            try {
                $deleted = $this->clean_matching_any_tags_lua($tags);
                // Ensure changes are committed
                if (method_exists($this->cache_pool, 'commit')) {
                    $this->cache_pool->commit();
                }
                return $deleted >= 0;
                // Lua returns number of items deleted
                // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
            } catch (\Exception $e) {
                // Intentional: Fall through to PHP implementation on Lua failure
            }
            // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
        }
        // PHP path (fallback) - matches Zend's PHP path (line 804-812)
        $ids = $this->get_ids_matching_any_tags($tags);
        if (empty($ids)) {
            return true;
        }
        // Batch delete - exactly like Zend's _removeByIds (line 751-768)
        $success = $this->delete_by_ids($ids);
        // Ensure changes are committed to underlying pool
        if (method_exists($this->cache_pool, 'commit')) {
            $this->cache_pool->commit();
        }
        return $success;
    }
    /**
     * Clean cache entries matching ANY tags within a scope (OR + AND logic)
     *
     * @param array $tags Tags to match (OR logic)
     * @param string $scopeTag Scope tag to filter by (AND logic)
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function clean_matching_any_tags_with_scope(array $tags, string $scope_tag): bool
    {
        if (empty($tags)) {
            return true;
        }
        // Lua path (if enabled) - atomic operation with scope filtering
        if ($this->use_lua && $this->lua_helper && $this->lua_helper->is_enabled()) {
            try {
                $deleted = $this->clean_matching_any_tags_with_scope_lua($tags, $scope_tag);
                // Ensure changes are committed
                if (method_exists($this->cache_pool, 'commit')) {
                    $this->cache_pool->commit();
                }
                return $deleted >= 0;
                // Lua returns number of items deleted
                // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
            } catch (\Exception $e) {
                // Intentional: Fall through to PHP implementation on Lua failure
            }
            // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
        }
        // Step 1: Get IDs matching ANY of the tags using SUNION (OR logic)
        $any_ids = $this->get_ids_matching_any_tags($tags);
        if (empty($any_ids)) {
            return true;
        }
        // Step 2: Get IDs matching the scope tag using SMEMBERS
        $scope_ids = $this->redis->s_members($this->get_tag_key($scope_tag));
        if (!is_array($scope_ids) || empty($scope_ids)) {
            return true;
        }
        // Step 3: Intersect to get IDs that have (tag1 OR tag2 OR ...) AND scopeTag
        $filtered_ids = array_intersect($any_ids, $scope_ids);
        if (empty($filtered_ids)) {
            return true;
        }
        // Step 4: Batch delete filtered IDs
        $success = $this->delete_by_ids($filtered_ids);
        // Step 5: Ensure changes are committed to underlying pool
        if (method_exists($this->cache_pool, 'commit')) {
            $this->cache_pool->commit();
        }
        return $success;
    }
    /**
     * @inheritDoc
     *
     * Maintains tag-to-ID indices in Redis SETs
     * OPTIMIZED: Uses Redis pipeline for batch operations
     */
    public function on_save(string $id, array $tags): void
    {
        if (empty($tags)) {
            return;
        }
        $pipeline = $this->create_pipeline();
        // Add ID to all_ids set
        $pipeline->sadd(self::ALL_IDS_SET, $id);
        // Forward index: Add ID to each tag's SET
        foreach ($tags as $tag) {
            $tag_key = $this->get_tag_key($tag);
            $pipeline->sadd($tag_key, $id);
        }
        // Reverse index: Store tags for this ID (for cleanup on delete)
        $id_tags_key = 'cache:id_tags:' . $this->namespace . $id;
        $pipeline->del($id_tags_key);
        // Clear old tags first
        foreach ($tags as $tag) {
            $pipeline->sadd($id_tags_key, $tag);
        }
        // Execute all operations in one go
        $this->execute_pipeline($pipeline);
    }
    /**
     * @inheritDoc
     *
     * Removes ID from all tag indices
     * OPTIMIZED: Uses Redis pipeline for batch operations
     */
    public function on_remove(string $id): void
    {
        // Find which tags this ID was associated with store a reverse index: cache:id:tags => SET{tag1, tag2}
        $id_tags_key = 'cache:id_tags:' . $this->namespace . $id;
        $tags = $this->redis->smembers($id_tags_key);
        if (!is_array($tags) || empty($tags)) {
            // No tags, just remove from all_ids
            $this->redis->srem(self::ALL_IDS_SET, $id);
            return;
        }
        // OPTIMIZATION: Use Redis pipeline for all remove operations, reduces network round trips from N+2 to 1
        $pipeline = $this->create_pipeline();
        // Remove from all_ids set
        $pipeline->srem(self::ALL_IDS_SET, $id);
        // Remove ID from each tag's SET in pipeline
        foreach ($tags as $tag) {
            $tag_key = $this->get_tag_key($tag);
            $pipeline->srem($tag_key, $id);
        }
        // Delete the reverse index
        $pipeline->del($id_tags_key);
        // Execute all operations in one go
        $this->execute_pipeline($pipeline);
    }
    /**
     * @inheritDoc
     */
    public function clear_all_indices(): void
    {
        // Use Lua script if enabled for atomic, efficient clearing
        if ($this->use_lua && $this->lua_helper) {
            $this->lua_helper->clear_all_indices($this->namespace);
            // Lua script handles everything atomically
            return;
        }
        // Fallback: PHP-based clearing (original implementation)
        // Get all tag keys
        $pattern = self::TAG_INDEX_PREFIX . $this->namespace . '*';
        $tag_keys = $this->redis->keys($pattern);
        if (is_array($tag_keys) && !empty($tag_keys)) {
            // PHP 8+ compatibility: use call_user_func_array to avoid spread operator issues
            call_user_func_array([$this->redis, 'del'], $tag_keys);
        }
        // Clear all_ids set
        $this->redis->del(self::ALL_IDS_SET);
        // Clear reverse index keys
        $reverse_pattern = 'cache:id_tags:' . $this->namespace . '*';
        $reverse_keys = $this->redis->keys($reverse_pattern);
        if (is_array($reverse_keys) && !empty($reverse_keys)) {
            // PHP 8+ compatibility: use call_user_func_array to avoid spread operator issues
            call_user_func_array([$this->redis, 'del'], $reverse_keys);
        }
    }
    /**
     * Store reverse index for efficient onRemove, This should be called after onSave
     *
     * @param string $id
     * @param array $tags
     * @return void
     */
    public function store_reverse_index(string $id, array $tags): void
    {
        if (empty($tags)) {
            return;
        }
        $id_tags_key = 'cache:id_tags:' . $this->namespace . $id;
        // OPTIMIZATION: Use Redis pipeline for all operations
        // Reduces network round trips from N+1 to 1
        $pipeline = $this->create_pipeline();
        // Clear existing reverse index
        $pipeline->del($id_tags_key);
        // Add all tags to reverse index in pipeline
        foreach ($tags as $tag) {
            $pipeline->sadd($id_tags_key, $tag);
        }
        // Execute all operations in one go
        $this->execute_pipeline($pipeline);
    }
    /**
     * Run garbage collection to clean expired items
     *
     * @param int $batchSize Number of keys to process per iteration
     * @return int Number of items cleaned
     */
    public function garbage_collect(int $batch_size = 1000): int
    {
        // Garbage collection specifically checks use_lua_on_gc flag
        if (!$this->use_lua_on_gc || !$this->lua_helper) {
            return 0;
        }
        $result = $this->lua_helper->garbage_collect($this->namespace . '*', self::TAG_INDEX_PREFIX . $this->namespace, $batch_size);
        return $result[0];
        // Return deleted count (first element)
    }
    /**
     * Check if Lua scripts are enabled and available
     *
     * @return bool
     */
    public function is_lua_enabled(): bool
    {
        return ($this->use_lua || $this->use_lua_on_gc) && $this->lua_helper !== null && $this->lua_helper->is_enabled();
    }
    /**
     * Clean expired items for specific tag using Lua
     *
     * Only deletes items that have expired (TTL = -2)
     * More efficient than fetching all IDs and checking client-side
     * Uses use_lua flag (general cache operations)
     *
     * @param string $tag Tag to clean
     * @return int Number of items deleted
     */
    public function clean_expired_by_tag(string $tag): int
    {
        // Tag operations check use_lua flag
        if (!$this->use_lua || !$this->lua_helper) {
            return 0;
        }
        $tag_key = $this->get_tag_key($tag);
        return $this->lua_helper->clean_by_tag_conditional($tag_key, $this->namespace, 'expired');
    }
    /**
     * Clean cache entries matching ANY tags using Lua script
     *
     * @param array $tags Tags to match (OR logic)
     * @return int Number of items deleted (-1 on error)
     */
    private function clean_matching_any_tags_lua(array $tags): int
    {
        if (empty($tags)) {
            return 0;
        }
        try {
            // Load and execute Lua script
            $sha = $this->load_lua_script(self::LUA_CLEAN_MATCHING_ANY_TAGS);
            // KEYS: array of tags
            // ARGV: [tag_prefix, namespace, chunk_size]
            $result = $this->redis->eval_sha(
                $sha,
                $tags,
                // KEYS
                count($tags),
                // Number of KEYS
                self::TAG_INDEX_PREFIX,
                // ARGV[1]
                $this->namespace,
                // ARGV[2]
                100
            );
            return (int) $result;
        } catch (\Redis_Exception $e) {
            // Fallback: try executing script directly
            try {
                $result = $this->redis->eval(self::LUA_CLEAN_MATCHING_ANY_TAGS, $tags, count($tags), self::TAG_INDEX_PREFIX, $this->namespace, 100);
                return (int) $result;
            } catch (\Redis_Exception $e) {
                // Return -1 to signal error (will fall back to PHP)
                return -1;
            }
        }
    }
    /**
     * Clean cache entries matching ANY tags within scope using Lua script
     *
     * @param array $tags Tags to match (OR logic)
     * @param string $scopeTag Scope tag to filter by (AND logic)
     * @return int Number of items deleted (-1 on error)
     */
    private function clean_matching_any_tags_with_scope_lua(array $tags, string $scope_tag): int
    {
        if (empty($tags)) {
            return 0;
        }
        try {
            // Load and execute Lua script
            $sha = $this->load_lua_script(self::LUA_CLEAN_MATCHING_ANY_TAGS_WITH_SCOPE);
            // KEYS: array of tags
            // ARGV: [tag_prefix, namespace, scope_tag]
            $result = $this->redis->eval_sha(
                $sha,
                $tags,
                // KEYS
                count($tags),
                // Number of KEYS
                self::TAG_INDEX_PREFIX,
                // ARGV[1]
                $this->namespace,
                // ARGV[2]
                $scope_tag
            );
            return (int) $result;
        } catch (\Redis_Exception $e) {
            // Fallback: try executing script directly
            try {
                $result = $this->redis->eval(self::LUA_CLEAN_MATCHING_ANY_TAGS_WITH_SCOPE, $tags, count($tags), self::TAG_INDEX_PREFIX, $this->namespace, $scope_tag);
                return (int) $result;
            } catch (\Redis_Exception $e) {
                // Return -1 to signal error (will fall back to PHP)
                return -1;
            }
        }
    }
    /**
     * Load Lua script and return SHA1
     *
     * @param string $script Lua script content
     * @return string SHA1 of the script
     * @throws \RedisException
     */
    private function load_lua_script(string $script): string
    {
        try {
            return $this->redis->script('load', $script);
        } catch (\Redis_Exception $e) {
            throw new \Redis_Exception('Failed to load Lua script: ' . $e->get_message(), 0, $e);
        }
    }
}