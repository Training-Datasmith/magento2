<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\App\Cache\Frontend\Pool;
/**
 * In-memory readonly pool of cache front-ends with enforced access control, specific to cache types
 *
 * @api
 * @since 100.0.2
 */
class Frontend_Pool
{
    /**
     * Array key for cache type
     */
    public const KEY_CACHE_TYPE = 'type';
    /**
     * Array key for cache frontend
     */
    public const KEY_FRONTEND_CACHE = 'frontend';
    /**
     * Config key for cache
     */
    public const KEY_CACHE = 'cache';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_object_manager;
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deployment_config;
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $_frontend_pool;
    /**
     * @var array
     */
    private $_type_frontend_map;
    /**
     * @var \Magento\Framework\Cache\FrontendInterface[]
     */
    private $_instances = [];
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\App\Cache\Frontend\Pool $frontendPool
     * @param array $typeFrontendMap Format: array('<cache_type_id>' => '<cache_frontend_id>', ...)
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, \Magento\Framework\App\Deployment_Config $deployment_config, \Magento\Framework\App\Cache\Frontend\Pool $frontend_pool, array $type_frontend_map = [])
    {
        $this->_object_manager = $object_manager;
        $this->deployment_config = $deployment_config;
        $this->_frontend_pool = $frontend_pool;
        $this->_type_frontend_map = $type_frontend_map;
    }
    /**
     * Retrieve cache frontend instance by a cache type identifier, enforcing identifier-scoped access control
     *
     * @param string $cacheType Cache type identifier
     * @return \Magento\Framework\Cache\FrontendInterface Cache frontend instance
     */
    public function get($cache_type)
    {
        if (!isset($this->_instances[$cache_type])) {
            $frontend_id = $this->_get_cache_frontend_id($cache_type);
            $frontend_instance = $this->_frontend_pool->get($frontend_id);
            /** @var $frontendInstance AccessProxy */
            $frontend_instance = $this->_object_manager->create(\Magento\Framework\App\Cache\Type\Access_Proxy::class, ['frontend' => $frontend_instance, 'identifier' => $cache_type]);
            $this->_instances[$cache_type] = $frontend_instance;
        }
        return $this->_instances[$cache_type];
    }
    /**
     * Retrieve cache frontend identifier, associated with a cache type
     *
     * @param string $cacheType
     * @return string
     */
    protected function _get_cache_frontend_id($cache_type)
    {
        $result = null;
        $cache_info = $this->deployment_config->get_config_data(self::KEY_CACHE);
        if (null !== $cache_info) {
            $result = isset($cache_info[self::KEY_CACHE_TYPE][$cache_type][self::KEY_FRONTEND_CACHE]) ? $cache_info[self::KEY_CACHE_TYPE][$cache_type][self::KEY_FRONTEND_CACHE] : null;
        }
        if (!$result) {
            if (isset($this->_type_frontend_map[$cache_type])) {
                $result = $this->_type_frontend_map[$cache_type];
            } else {
                $result = \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID;
            }
        }
        return $result;
    }
}