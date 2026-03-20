<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Cache\Type;

/**
 * System / Cache Management / Cache type "Layouts"
 */
class Layout extends \Magento\Framework\Cache\Frontend\Decorator\Tag_Scope
{
    /**
     * Prefix for hash kay and hash data
     */
    public const HASH_PREFIX = 'l:';
    /**
     * Hash type, not used for security, only for uniqueness
     */
    public const HASH_TYPE = 'xxh3';
    /**
     * Data lifetime in milliseconds
     */
    public const DATA_LIFETIME = 86400000;
    // "1 day" milliseconds
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'layout';
    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'LAYOUT_GENERAL_CACHE_TAG';
    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(Frontend_Pool $cache_frontend_pool)
    {
        parent::__construct($cache_frontend_pool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $life_time = null)
    {
        $data_hash = hash(self::HASH_TYPE, $data);
        $identifier_for_hash = self::HASH_PREFIX . $data_hash;
        return parent::save($data, $identifier_for_hash, $tags, self::DATA_LIFETIME) && parent::save(self::HASH_PREFIX . $data_hash, $identifier, $tags, $life_time);
        // store hash of data
    }
    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        $data = parent::load($identifier);
        if ($data === false || $data === null) {
            return $data;
        }
        if (str_starts_with($data, self::HASH_PREFIX)) {
            // so data stored in other place
            return parent::load($data);
        } else {
            return $data;
        }
    }
}