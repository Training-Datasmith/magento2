<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Cache_Invalidate\Observer;

use Magento\Cache_Invalidate\Model\Purge_Cache;
use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\Observer_Interface;
use Magento\Page_Cache\Model\Config;
/**
 * Observer used to invalidate varnish cache once Magento cache was cleaned
 */
class Invalidate_Varnish_Observer implements Observer_Interface
{
    /**
     * Application config object
     *
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var PurgeCache
     */
    private $purge_cache;
    /**
     * Invalidation tags resolver
     *
     * @var Resolver
     */
    private $tag_resolver;
    /**
     * @param Config $config
     * @param PurgeCache $purgeCache
     * @param Resolver $tagResolver
     */
    public function __construct(Config $config, Purge_Cache $purge_cache, Resolver $tag_resolver)
    {
        $this->config = $config;
        $this->purge_cache = $purge_cache;
        $this->tag_resolver = $tag_resolver;
    }
    /**
     * If Varnish caching is enabled it collects array of tags of incoming object and asks to clean cache.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $object = $observer->get_event()->get_object();
        if (!is_object($object)) {
            return;
        }
        if ((int) $this->config->get_type() === Config::VARNISH && $this->config->is_enabled()) {
            $bare_tags = $this->tag_resolver->get_tags($object);
            $tags = [];
            $pattern = '((^|,)%s(,|$))';
            foreach ($bare_tags as $tag) {
                $tags[] = sprintf($pattern, $tag);
            }
            if (!empty($tags)) {
                $this->purge_cache->send_purge_request(array_unique($tags));
            }
        }
    }
}