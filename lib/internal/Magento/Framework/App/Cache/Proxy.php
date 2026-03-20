<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Cache;

use Magento\Framework\App\Cache_Interface;
use Magento\Framework\Object_Manager\Noninterceptable_Interface;
/**
 * System cache proxy model
 */
class Proxy implements Cache_Interface, Noninterceptable_Interface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @var CacheInterface
     */
    protected $_cache;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Create cache model
     *
     * @return CacheInterface
     */
    protected function _get_cache()
    {
        if (null == $this->_cache) {
            $this->_cache = $this->_object_manager->get(\Magento\Framework\App\Cache::class);
        }
        return $this->_cache;
    }
    /**
     * Get cache frontend API object
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    public function get_frontend()
    {
        return $this->_get_cache()->get_frontend();
    }
    /**
     * Load data from cache by id
     *
     * @param  string $identifier
     * @return string
     */
    public function load($identifier)
    {
        return $this->_get_cache()->load($identifier);
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
        return $this->_get_cache()->save($data, $identifier, $tags, $life_time);
    }
    /**
     * Remove cached data by identifier
     *
     * @param string $identifier
     * @return bool
     */
    public function remove($identifier)
    {
        return $this->_get_cache()->remove($identifier);
    }
    /**
     * Clean cached data by specific tag
     *
     * @param array $tags
     * @return bool
     */
    public function clean($tags = [])
    {
        return $this->_get_cache()->clean($tags);
    }
}