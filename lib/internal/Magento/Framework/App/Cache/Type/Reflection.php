<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Type;

/**
 * System / Cache Management / Cache type "Reflection Data"
 */
class Reflection extends \Magento\Framework\Cache\Frontend\Decorator\Tag_Scope
{
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'reflection';
    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'REFLECTION';
    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Type\Frontend_Pool $cache_frontend_pool)
    {
        parent::__construct($cache_frontend_pool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}