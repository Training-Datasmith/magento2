<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Acl\Data;

use Magento\Framework\Cache\Cache_Constants;
/**
 * ACL data cache layer.
 */
class Cache implements Cache_Interface
{
    /**
     * Acl Data cache tag.
     */
    public const ACL_DATA_CACHE_TAG = 'acl_cache';
    /**
     * @var \Magento\Framework\Config\CacheInterface
     */
    private $cache;
    /**
     * @var \Magento\Framework\Acl\Builder
     */
    private $acl_builder;
    /**
     * @var string
     */
    private $cache_tag;
    /**
     * Cache constructor.
     *
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\Acl\Builder $aclBuilder
     * @param string $cacheTag
     */
    public function __construct(\Magento\Framework\Config\Cache_Interface $cache, \Magento\Framework\Acl\Builder $acl_builder, $cache_tag = self::ACL_DATA_CACHE_TAG)
    {
        $this->cache = $cache;
        $this->acl_builder = $acl_builder;
        $this->cache_tag = $cache_tag;
    }
    /**
     * @inheritDoc
     */
    public function test($identifier)
    {
        return $this->cache->test($identifier);
    }
    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        return $this->cache->load($identifier);
    }
    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $life_time = null)
    {
        return $this->cache->save($data, $identifier, array_merge($tags, [$this->cache_tag]), $life_time);
    }
    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        return $this->cache->remove($identifier);
    }
    /**
     * @inheritDoc
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_MATCHING_TAG, array $tags = [])
    {
        $this->acl_builder->reset_runtime_acl();
        return $this->cache->clean($mode, array_merge($tags, [$this->cache_tag]));
    }
    /**
     * @inheritDoc
     */
    public function get_backend()
    {
        return $this->cache->get_backend();
    }
    /**
     * @inheritDoc
     */
    public function get_low_level_frontend()
    {
        return $this->cache->get_low_level_frontend();
    }
}