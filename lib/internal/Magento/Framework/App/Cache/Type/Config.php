<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\Cache\Frontend\Decorator\Tag_Scope;
use Magento\Framework\Config\Cache_Interface;
/**
 * System / Cache Management / Cache type "Configuration"
 *
 * @api
 */
class Config extends Tag_Scope implements Cache_Interface
{
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'config';
    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'CONFIG';
    /**
     * @var \Magento\Framework\App\Cache\Type\FrontendPool
     */
    private $cache_frontend_pool;
    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Type\Frontend_Pool $cache_frontend_pool)
    {
        $this->cache_frontend_pool = $cache_frontend_pool;
    }
    /**
     * Retrieve cache frontend instance being decorated
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    protected function _get_frontend()
    {
        $frontend = parent::_get_frontend();
        if (!$frontend) {
            $frontend = $this->cache_frontend_pool->get(self::TYPE_IDENTIFIER);
            $this->set_frontend($frontend);
        }
        return $frontend;
    }
    /**
     * Retrieve cache tag name
     *
     * @return string
     */
    public function get_tag()
    {
        return self::CACHE_TAG;
    }
    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}