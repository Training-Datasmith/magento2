<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Adapter;

use Magento\Framework\App\Cache\Type\Frontend_Pool;
use Magento\Framework\Cache\Frontend\Decorator\Tag_Scope;
/**
 * Cache segment for DDL operations in database adapter
 */
class Ddl_Cache extends Tag_Scope
{
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'db_ddl';
    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'DB_DDL';
    /**
     * Constructor
     *
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(Frontend_Pool $cache_frontend_pool)
    {
        parent::__construct($cache_frontend_pool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}