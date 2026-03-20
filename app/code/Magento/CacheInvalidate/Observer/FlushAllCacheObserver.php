<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cache_Invalidate\Observer;

use Magento\Framework\Event\Observer_Interface;
/**
 * Clear configured Varnish hosts when triggering a full cache flush (e.g. from the Cache Management admin dashboard)
 */
class Flush_All_Cache_Observer implements Observer_Interface
{
    /**
     * Application config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;
    /**
     * @var \Magento\CacheInvalidate\Model\PurgeCache
     */
    protected $purge_cache;
    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\CacheInvalidate\Model\PurgeCache $purgeCache
     */
    public function __construct(\Magento\Page_Cache\Model\Config $config, \Magento\Cache_Invalidate\Model\Purge_Cache $purge_cache)
    {
        $this->config = $config;
        $this->purge_cache = $purge_cache;
    }
    /**
     * Flash Varnish cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->get_type() == \Magento\Page_Cache\Model\Config::VARNISH && $this->config->is_enabled()) {
            $this->purge_cache->send_purge_request(['.*']);
        }
    }
}