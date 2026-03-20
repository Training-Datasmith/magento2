<?php

/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Cache;

use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\App\Cache\Type\Frontend_Pool;
use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Model\Abstract_Model;
use Magento\Framework\Model\Resource_Model\Abstract_Resource;
/**
 * Automatic cache cleaner plugin
 */
class Flush_Cache_By_Tags
{
    /**
     * @var FrontendPool
     */
    private $cache_pool;
    /**
     * @var array
     */
    private $cache_list;
    /**
     * @var StateInterface
     */
    private $cache_state;
    /**
     * @var Resolver
     */
    private $tag_resolver;
    /**
     * @param FrontendPool $cachePool
     * @param StateInterface $cacheState
     * @param string[] $cacheList
     * @param Resolver $tagResolver
     */
    public function __construct(Frontend_Pool $cache_pool, State_Interface $cache_state, array $cache_list, Resolver $tag_resolver)
    {
        $this->cache_pool = $cache_pool;
        $this->cache_state = $cache_state;
        $this->cache_list = $cache_list;
        $this->tag_resolver = $tag_resolver;
    }
    /**
     * Clean cache when object is saved
     *
     * @param AbstractResource $subject
     * @param AbstractResource $result
     * @param AbstractModel $object
     * @return AbstractResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_save(Abstract_Resource $subject, Abstract_Resource $result, Abstract_Model $object): Abstract_Resource
    {
        $tags = $this->tag_resolver->get_tags($object);
        $this->clean_cache_by_tags($tags);
        return $result;
    }
    /**
     * Clean cache when object is deleted
     *
     * @param AbstractResource $subject
     * @param AbstractResource $result
     * @param AbstractModel $object
     * @return AbstractResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_delete(Abstract_Resource $subject, Abstract_Resource $result, Abstract_Model $object): Abstract_Resource
    {
        $tags = $this->tag_resolver->get_tags($object);
        $this->clean_cache_by_tags($tags);
        return $result;
    }
    /**
     * Clean cache by tags
     *
     * @param string[] $tags
     * @return void
     */
    private function clean_cache_by_tags(array $tags): void
    {
        if (!$tags) {
            return;
        }
        $unique_tags = null;
        foreach ($this->cache_list as $cache_type) {
            if ($this->cache_state->is_enabled($cache_type)) {
                $this->cache_pool->get($cache_type)->clean(Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG, $unique_tags = $unique_tags ?? \array_unique($tags));
            }
        }
    }
}