<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Interception\Cache;

use Magento\Framework\App\Cache\Type\Frontend_Pool;
use Magento\Framework\Cache\Frontend\Decorator\Tag_Scope;
use Magento\Framework\Config\Cache_Interface;
class Compiled_Config extends Tag_Scope implements Cache_Interface
{
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'compiled_config';
    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'COMPILED_CONFIG';
    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(Frontend_Pool $cache_frontend_pool)
    {
        parent::__construct($cache_frontend_pool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}