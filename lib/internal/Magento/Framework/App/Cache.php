<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\Cache\Cache_Constants;
/**
 * System cache model support id and tags prefix support.
 */
class Cache implements Cache_Interface
{
    /**
     * @var string
     */
    protected $_frontend_identifier = \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID;
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_frontend_pool;
    /**
     * Cache frontend API
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_frontend;
    /**
     * @param Cache\Frontend\Pool $frontendPool
     * @param string|null $cacheIdentifier
     */
    public function __construct(\Magento\Framework\App\Cache\Frontend\Pool $frontend_pool, $cache_identifier = null)
    {
        $this->_frontend_pool = $frontend_pool;
        $this->_frontend = $frontend_pool->get($cache_identifier ?? $this->_frontend_identifier);
    }
    /**
     * Get cache frontend API object
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    public function get_frontend()
    {
        return $this->_frontend;
    }
    /**
     * Load data from cache by id
     *
     * @param  string $identifier
     * @return string
     */
    public function load($identifier)
    {
        return $this->_frontend->load($identifier);
    }
    /**
     * Save data
     *
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param int $lifeTime
     * @return bool
     */
    public function save($data, $identifier, $tags = [], $life_time = null)
    {
        return $this->_frontend->save((string) $data, $identifier, $tags, $life_time);
    }
    /**
     * Remove cached data by identifier
     *
     * @param string $identifier
     * @return bool
     */
    public function remove($identifier)
    {
        return $this->_frontend->remove($identifier);
    }
    /**
     * Clean cached data by specific tag
     *
     * @param array $tags
     * @return bool
     */
    public function clean($tags = [])
    {
        if ($tags) {
            $result = $this->_frontend->clean(Cache_Constants::CLEANING_MODE_MATCHING_ANY_TAG, (array) $tags);
        } else {
            /** @deprecated special case of cleaning by empty tags is deprecated after 2.0.0.0-dev42 */
            $result = false;
            /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
            foreach ($this->_frontend_pool as $cache_frontend) {
                if ($cache_frontend->clean()) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}