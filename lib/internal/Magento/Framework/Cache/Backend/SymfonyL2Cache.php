<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Backend;

use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Exception\Cache_Exception;
use Magento\Framework\Cache\Frontend_Interface;
/**
 * L2 (Two-Level) Cache Backend for Symfony Adapters
 *
 * This backend provides local + remote caching with automatic synchronization,
 * designed specifically for Symfony cache adapters (PSR-6 compliant).
 *
 * Unlike RemoteSynchronizedCache (which requires ExtendedBackendInterface),
 * this class works directly with Symfony's FrontendInterface.
 *
 * Architecture:
 * - L1 (Local): Fast cache (file/APCu) - Per worker, ephemeral
 * - L2 (Remote): Persistent cache (Redis/Valkey) - Shared, persistent
 * - Sync: :hash mechanism detects stale local data
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Symfony_L2cache extends Abstract_Backend implements Extended_Backend_Interface
{
    /**
     * Local backend cache (L1)
     *
     * @var FrontendInterface
     */
    private Frontend_Interface $local;
    /**
     * Remote backend cache (L2)
     *
     * @var FrontendInterface
     */
    private Frontend_Interface $remote;
    /**
     * Suffix for hash to compare data version in cache storage
     */
    private const HASH_SUFFIX = ':hash';
    /**
     * Default cleanup percentage for L1 cache
     */
    private const DEFAULT_CLEANUP_PERCENTAGE = 90;
    /**
     * Cleanup percentage threshold (when to trigger L1 cleanup)
     *
     * @var int
     */
    private int $cleanup_percentage;
    /**
     * Whether to use stale cache when remote (L2) is unavailable
     *
     * @var bool
     */
    private bool $use_stale_cache;
    /**
     * Key prefix for tracking invalid entries in local cache
     */
    private const INVALID_KEY_PREFIX = '__invalid::';
    /**
     * TTL for invalid markers for 24 hours
     */
    private const INVALID_MARK_TTL = 86400;
    /**
     * Constructor
     *
     * @param FrontendInterface $remote Remote cache (L2 - persistent, shared)
     * @param FrontendInterface $local Local cache (L1 - fast, per-worker)
     * @param array $options Additional options
     * @throws CacheException
     */
    public function __construct(Frontend_Interface $remote, Frontend_Interface $local, array $options = [])
    {
        parent::__construct($options);
        $this->remote = $remote;
        $this->local = $local;
        $this->cleanup_percentage = (int) ($options['cleanup_percentage'] ?? self::DEFAULT_CLEANUP_PERCENTAGE);
        $this->use_stale_cache = (bool) ($options['use_stale_cache'] ?? false);
        // Validate cleanup percentage
        if ($this->cleanup_percentage < 1 || $this->cleanup_percentage > 100) {
            throw new Cache_Exception(__('cleanup_percentage must be between 1 and 100'));
        }
    }
    /**
     * @inheritDoc
     */
    public function load($id, $do_not_test_cache_validity = false)
    {
        // Try local cache first (fast path)
        $local_data = $this->local->load($id);
        if ($this->is_invalid($id)) {
            return $this->handle_invalid_key($id);
        }
        if ($local_data !== false) {
            $result = $this->validate_local_cache($id, $local_data);
            if ($result !== null) {
                return $result;
            }
            // Local cache is stale, fall through to load from remote
        }
        return $this->load_from_remote_or_fallback($id, $local_data);
    }
    /**
     * @inheritDoc
     */
    public function test($id)
    {
        if ($this->use_stale_cache) {
            // With stale cache, check local first for availability
            return $this->local->test($id) ?: $this->remote->test($id);
        }
        // Check remote cache (source of truth)
        return $this->remote->test($id);
    }
    /**
     * @inheritDoc
     */
    public function save($data, $id, $tags = [], $specific_lifetime = null)
    {
        $hash_saved = false;
        try {
            // Save data first to avoid hash pointing to non-existent data
            $remote_saved = $this->remote->save($data, $id, $tags, $specific_lifetime);
            if ($remote_saved !== false) {
                // Calculate and save hash to remote for synchronization
                $hash = $this->get_data_hash($data);
                $hash_saved = $this->remote->save($hash, $id . self::HASH_SUFFIX, $tags, $specific_lifetime);
            }
        } catch (\Exception $e) {
            $remote_saved = false;
            $hash_saved = false;
        }
        // Save to local cache
        $this->local->save($data, $id, $tags, $specific_lifetime);
        if ($remote_saved !== false && $hash_saved !== false) {
            $this->mark_valid($id);
        } else if ($this->use_stale_cache) {
            $this->mark_invalid($id);
        }
        return $remote_saved;
    }
    /**
     * @inheritDoc
     */
    public function remove($id)
    {
        try {
            // Remove hash from remote
            $hash_removed = $this->remote->remove($id . self::HASH_SUFFIX);
            // Remove from remote
            $result = $this->remote->remove($id);
        } catch (\Exception $e) {
            $hash_removed = false;
            $result = false;
        }
        // Only remove from local if NOT using stale cache (keep stale data for availability)
        if (!$this->use_stale_cache) {
            $this->local->remove($id);
        }
        if ($result !== false && $hash_removed !== false) {
            $this->mark_valid($id);
        } else if ($this->use_stale_cache) {
            $this->mark_invalid($id);
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, $tags = [])
    {
        // Clean both caches
        $this->local->clean($mode, $tags);
        return $this->remote->clean($mode, $tags);
    }
    /**
     * Calculate hash of data for synchronization
     *
     * @param string $data
     * @return string
     */
    private function get_data_hash(string $data): string
    {
        return hash('sha256', $data);
    }
    /**
     * @inheritDoc
     */
    public function get_ids()
    {
        // Return IDs from remote (source of truth)
        // Note: This may not be supported by all Symfony adapters
        return [];
    }
    /**
     * @inheritDoc
     */
    public function get_tags()
    {
        // Return tags from remote
        // Note: This may not be supported by all Symfony adapters
        return [];
    }
    /**
     * @inheritDoc
     */
    public function get_ids_matching_tags($tags = [])
    {
        // Not supported by Symfony adapters
        return [];
    }
    /**
     * @inheritDoc
     */
    public function get_ids_not_matching_tags($tags = [])
    {
        // Not supported by Symfony adapters
        return [];
    }
    /**
     * @inheritDoc
     */
    public function get_ids_matching_any_tags($tags = [])
    {
        // Not supported by Symfony adapters
        return [];
    }
    /**
     * @inheritDoc
     */
    public function get_filling_percentage()
    {
        // Cannot determine filling percentage for L2 cache
        return 0;
    }
    /**
     * @inheritDoc
     */
    public function get_metadatas($id)
    {
        // Get test result (timestamp)
        $mtime = $this->remote->test($id);
        if ($mtime === false) {
            return false;
        }
        return ['expire' => null, 'tags' => [], 'mtime' => $mtime];
    }
    /**
     * @inheritDoc
     */
    public function touch($id, $extra_lifetime)
    {
        // Reload and resave with extended lifetime
        $data = $this->remote->load($id);
        if ($data === false) {
            return false;
        }
        return $this->save($data, $id, [], $extra_lifetime);
    }
    /**
     * @inheritDoc
     */
    public function get_capabilities()
    {
        return ['automatic_cleaning' => false, 'tags' => true, 'expired_read' => false, 'priority' => false, 'infinite_lifetime' => true, 'get_list' => false];
    }
    /**
     * Get remote cache frontend
     *
     * @return FrontendInterface
     */
    public function get_remote(): Frontend_Interface
    {
        return $this->remote;
    }
    /**
     * Get local cache frontend
     *
     * @return FrontendInterface
     */
    public function get_local(): Frontend_Interface
    {
        return $this->local;
    }
    /**
     * Check if a cache key was modified while remote was unavailable
     *
     * @param string $id
     * @return bool
     */
    private function is_invalid(string $id): bool
    {
        return $this->local->load(self::INVALID_KEY_PREFIX . $id) !== false;
    }
    /**
     * Mark a cache key as invalid (modified while remote was unavailable)
     *
     * @param string $id
     * @return void
     */
    private function mark_invalid(string $id): void
    {
        $this->local->save('1', self::INVALID_KEY_PREFIX . $id, [], self::INVALID_MARK_TTL);
    }
    /**
     * Mark a cache key as valid (synchronized with remote)
     *
     * @param string $id
     * @return void
     */
    private function mark_valid(string $id): void
    {
        $this->local->remove(self::INVALID_KEY_PREFIX . $id);
    }
    /**
     * Clean an invalid key from remote cache
     *
     * @param string $id
     * @return bool
     */
    private function clean_invalid_from_remote(string $id): bool
    {
        try {
            $this->remote->remove($id . self::HASH_SUFFIX);
            $this->remote->remove($id);
            return true;
        } catch (\Exception $e) {
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            // If remote is still unavailable, the invalid marker will be cleared anyway
            return false;
        }
    }
    /**
     * Handle invalid key by cleaning from remote and local
     *
     * @param string $id
     * @return false
     */
    private function handle_invalid_key(string $id)
    {
        $remote_clean_success = $this->clean_invalid_from_remote($id);
        $this->local->remove($id);
        if ($remote_clean_success) {
            $this->mark_valid($id);
        }
        return false;
    }
    /**
     * Validate local cache data against remote hash
     *
     * @param string $id
     * @param string $localData
     * @return string|false|null Returns data if valid, false if invalid, null if stale (should try remote)
     */
    private function validate_local_cache(string $id, string $local_data)
    {
        $remote_hash = $this->remote->load($id . self::HASH_SUFFIX);
        if ($remote_hash === false && $this->use_stale_cache) {
            return $local_data;
        }
        $local_hash = $this->get_data_hash($local_data);
        if ($remote_hash === $local_hash) {
            return $local_data;
        }
        return null;
    }
    /**
     * Load from remote cache or fallback to stale local data
     *
     * @param string $id
     * @param string|false $localData
     * @return string|false
     */
    private function load_from_remote_or_fallback(string $id, $local_data)
    {
        $remote_data = $this->remote->load($id);
        if ($remote_data !== false) {
            $this->local->save($remote_data, $id);
            return $remote_data;
        }
        if ($local_data && $this->use_stale_cache) {
            return $local_data;
        }
        return false;
    }
}