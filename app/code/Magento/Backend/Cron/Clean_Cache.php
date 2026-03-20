<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Cron;

use Magento\Framework\Cache\Cache_Constants;
/**
 * Backend event observer
 */
class Clean_Cache
{
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $cache_frontend_pool;
    /**
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Frontend\Pool $cache_frontend_pool)
    {
        $this->cache_frontend_pool = $cache_frontend_pool;
    }
    /**
     * Cron job method to clean old cache resources
     *
     * @return void
     */
    public function execute()
    {
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($this->cache_frontend_pool as $cache_frontend) {
            // Clean old/expired cache entries - Symfony cache handles this automatically
            $cache_frontend->clean(Cache_Constants::CLEANING_MODE_OLD);
        }
    }
}