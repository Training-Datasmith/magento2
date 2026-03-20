<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Cache\Backend\Database;
use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Serialize\Serializer\Serialize;
use Psr\Cache\Cache_Item_Interface;
use Symfony\Component\Cache\Adapter\Adapter_Interface;
use Symfony\Component\Cache\Cache_Item;
/**
 * PSR-6 adapter for Magento's Database backend
 *
 * Wraps Magento\Framework\Cache\Backend\Database to make it PSR-6 compliant.
 * Allows using Magento's existing cache/cache_tag tables with Symfony architecture.
 */
class Magento_Database_Adapter implements Adapter_Interface
{
    /**
     * @var Database
     */
    private Database $backend;
    /**
     * @var Serialize PHP native serializer (required for binary tag versions)
     */
    private Serialize $serializer;
    /**
     * @var array Deferred items to save
     */
    private array $deferred = [];
    /**
     * @var string Namespace prefix for cache keys
     */
    private string $namespace;
    /**
     * @var int Default lifetime in seconds
     */
    private int $default_lifetime;
    /**
     * Constructor
     *
     * @param ResourceConnection $resource
     * @param Serialize $serializer PHP native serializer
     * @param string $namespace
     * @param int $defaultLifetime
     */
    public function __construct(Resource_Connection $resource, Serialize $serializer, string $namespace = '', int $default_lifetime = 0)
    {
        $this->serializer = $serializer;
        $this->namespace = $namespace;
        $this->default_lifetime = $default_lifetime;
        // Create Database backend with Magento's resource connection
        $this->backend = new Database(['adapter' => $resource->get_connection(), 'data_table' => $resource->get_table_name('cache'), 'tags_table' => $resource->get_table_name('cache_tag'), 'store_data' => true]);
    }
    /**
     * @inheritDoc
     */
    public function get_item(mixed $key): Cache_Item
    {
        $prefixed_key = $this->get_prefixed_key($key);
        $serialized_data = $this->backend->load($prefixed_key);
        // Database backend returns serialized strings - we need to unserialize them
        $value = null;
        $is_hit = false;
        $tag_versions = [];
        $expiry = null;
        if ($serialized_data !== false) {
            // Unserialize the data structure using Magento's serializer
            $unserialized = $this->serializer->unserialize($serialized_data);
            if ($unserialized !== false && is_array($unserialized)) {
                // New format with tag_versions
                if (isset($unserialized['data'])) {
                    $value = $unserialized['data'];
                    $tag_versions = $unserialized['tag_versions'] ?? [];
                    if (isset($unserialized['expire'])) {
                        $expiry = (float) $unserialized['expire'];
                    }
                    $is_hit = true;
                } else {
                    // Fallback for old format (backward compatibility)
                    $value = $unserialized;
                    if (isset($unserialized['tags'])) {
                        // Old format stores tags, create tag name => tag name mapping
                        $tags = is_array($unserialized['tags']) ? $unserialized['tags'] : [];
                        $tag_versions = array_combine($tags, $tags);
                    }
                    if (isset($unserialized['expire'])) {
                        $expiry = (float) $unserialized['expire'];
                    }
                    $is_hit = true;
                }
            } else {
                // Simple value (non-array)
                $value = $unserialized;
                $is_hit = true;
            }
        }
        $item = new Cache_Item();
        $this->set_cache_item_state($item, $key, $value, $is_hit, $tag_versions, $expiry);
        return $item;
    }
    /**
     * @inheritDoc
     */
    public function get_items(array $keys = []): iterable
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->get_item($key);
        }
        return $items;
    }
    /**
     * @inheritDoc
     */
    public function has_item(string $key): bool
    {
        $prefixed_key = $this->get_prefixed_key($key);
        return $this->backend->test($prefixed_key) !== false;
    }
    /**
     * @inheritDoc
     */
    public function clear(string $prefix = ''): bool
    {
        return $this->backend->clean(Cache_Constants::CLEANING_MODE_ALL);
    }
    /**
     * @inheritDoc
     */
    public function delete_item(string $key): bool
    {
        $prefixed_key = $this->get_prefixed_key($key);
        return $this->backend->remove($prefixed_key) !== false;
    }
    /**
     * @inheritDoc
     */
    public function delete_items(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            $success = $this->delete_item($key) && $success;
        }
        return $success;
    }
    /**
     * @inheritDoc
     */
    public function save(Cache_Item_Interface $item): bool
    {
        $key = $item->get_key();
        $prefixed_key = $this->get_prefixed_key($key);
        // Get value
        $value = $this->get_cache_item_value($item);
        // Get tag versions from newMetadata (set by TagAwareAdapter)
        // TagAwareAdapter stores actual tag versions in newMetadata, not metadata
        $tag_versions = $this->extract_tag_versions($item);
        // Create data structure with value, tags, and expiry
        $expiration = $this->get_cache_item_expiration($item);
        $lifetime = $expiration !== null ? $expiration - time() : $this->default_lifetime;
        $expiry_time = $lifetime ? time() + $lifetime : 0;
        $data_structure = [
            'data' => $value,
            'tags' => array_keys($tag_versions),
            // Tag names for Database backend
            'tag_versions' => $tag_versions,
            // Actual tag versions with random bytes
            'mtime' => time(),
            'expire' => $expiry_time,
        ];
        // Serialize the complete structure using Magento's serializer
        $serialized_data = $this->serializer->serialize($data_structure);
        // Save to database backend
        return $this->backend->save($serialized_data, $prefixed_key, array_keys($tag_versions), $lifetime);
    }
    /**
     * @inheritDoc
     */
    public function save_deferred(Cache_Item_Interface $item): bool
    {
        $this->deferred[$item->get_key()] = $item;
        return true;
    }
    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        $success = true;
        foreach ($this->deferred as $item) {
            $success = $this->save($item) && $success;
        }
        $this->deferred = [];
        return $success;
    }
    /**
     * Get Magento's Database backend
     *
     * @return Database
     */
    public function get_backend(): Database
    {
        return $this->backend;
    }
    /**
     * Get prefixed cache key
     *
     * @param string $key
     * @return string
     */
    private function get_prefixed_key(string $key): string
    {
        return $this->namespace !== '' ? $this->namespace . $key : $key;
    }
    /**
     * Extract tag versions from CacheItem's newMetadata
     *
     * TagAwareAdapter stores actual tag versions (random bytes) in newMetadata,
     * not in the metadata returned by getMetadata()
     *
     * @param CacheItemInterface $item
     * @return array Tag versions in format ['TAG1' => 'version_bytes', 'TAG2' => 'version_bytes']
     */
    private function extract_tag_versions(Cache_Item_Interface $item): array
    {
        if (!$item instanceof Cache_Item) {
            return [];
        }
        try {
            $reflection = new \ReflectionClass($item);
            // Try newMetadata first (set by TagAwareAdapter during save)
            if ($reflection->has_property('newMetadata')) {
                $new_metadata_property = $reflection->get_property('newMetadata');
                $new_metadata = $new_metadata_property->get_value($item);
                if (isset($new_metadata[Cache_Item::METADATA_TAGS]) && is_array($new_metadata[Cache_Item::METADATA_TAGS])) {
                    return $new_metadata[Cache_Item::METADATA_TAGS];
                }
            }
            // Fallback to regular metadata
            $metadata = $item->get_metadata();
            if (isset($metadata[Cache_Item::METADATA_TAGS]) && is_array($metadata[Cache_Item::METADATA_TAGS])) {
                return $metadata[Cache_Item::METADATA_TAGS];
            }
            // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
        } catch (\Reflection_Exception $e) {
            // Unable to access metadata - silently fail and return empty array
            // This can happen if CacheItem structure changes in future Symfony versions
        }
        // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
        return [];
    }
    /**
     * Set cache item state using reflection
     *
     * @param CacheItem $item
     * @param string $key
     * @param mixed $value
     * @param bool $isHit
     * @param array $tagVersions Tag versions in format ['TAG1' => 'version_bytes']
     * @param float|null $expiry Expiration timestamp
     */
    private function set_cache_item_state(Cache_Item $item, string $key, $value, bool $is_hit, array $tag_versions = [], ?float $expiry = null): void
    {
        $reflection = new \ReflectionClass($item);
        // Set key
        $key_property = $reflection->get_property('key');
        $key_property->set_value($item, $key);
        // Set value
        $value_property = $reflection->get_property('value');
        $value_property->set_value($item, $value);
        // Set isHit
        $is_hit_property = $reflection->get_property('isHit');
        $is_hit_property->set_value($item, $is_hit);
        // Set expiry
        if ($expiry !== null && $expiry > 0) {
            $expiry_property = $reflection->get_property('expiry');
            $expiry_property->set_value($item, $expiry);
        }
        // Set metadata with tag versions for TagAwareAdapter compatibility
        // TagAwareAdapter expects metadata[METADATA_TAGS] = ['TAG1' => 'version', 'TAG2' => 'version']
        if (!empty($tag_versions)) {
            $metadata_property = $reflection->get_property('metadata');
            // Store tag versions exactly as provided (with actual version bytes)
            $metadata = [\Symfony\Component\Cache\Cache_Item::METADATA_TAGS => $tag_versions];
            $metadata_property->set_value($item, $metadata);
        }
    }
    /**
     * Get cache item value using reflection
     *
     * @param CacheItemInterface $item
     * @return mixed
     */
    private function get_cache_item_value(Cache_Item_Interface $item)
    {
        if ($item instanceof Cache_Item) {
            $reflection = new \ReflectionClass($item);
            $value_property = $reflection->get_property('value');
            return $value_property->get_value($item);
        }
        return $item->get();
    }
    /**
     * Get cache item expiration using reflection
     *
     * @param CacheItemInterface $item
     * @return int|null
     */
    private function get_cache_item_expiration(Cache_Item_Interface $item): ?int
    {
        if ($item instanceof Cache_Item) {
            $reflection = new \ReflectionClass($item);
            $expiry_property = $reflection->get_property('expiry');
            $expiry = $expiry_property->get_value($item);
            return $expiry !== null ? (int) $expiry : null;
        }
        return null;
    }
}