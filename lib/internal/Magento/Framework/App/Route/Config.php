<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Route;

use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Routes configuration model
 */
class Config implements Config_Interface, Reset_After_Request_Interface
{
    /**
     * @var \Magento\Framework\App\Route\Config\Reader
     */
    protected $_reader;
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;
    /**
     * @var string
     */
    protected $_cache_id;
    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_config_scope;
    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $_area_list;
    /**
     * @var array|null
     */
    protected $_routes;
    /**
     * @var SerializerInterface|null
     */
    private $serializer;
    /**
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\App\AreaList $areaList
     * @param string $cacheId
     */
    public function __construct(Config\Reader $reader, \Magento\Framework\Config\Cache_Interface $cache, \Magento\Framework\Config\Scope_Interface $config_scope, \Magento\Framework\App\Area_List $area_list, $cache_id = 'RoutesConfig')
    {
        $this->_reader = $reader;
        $this->_cache = $cache;
        $this->_cache_id = $cache_id;
        $this->_config_scope = $config_scope;
        $this->_area_list = $area_list;
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->_routes = null;
        $this->serializer = null;
    }
    /**
     * Fetch routes from configs by area code and router id
     *
     * @param string $scope
     * @return array
     */
    protected function _get_routes($scope = null)
    {
        $scope = $scope ?: $this->_config_scope->get_current_scope();
        if (isset($this->_routes[$scope])) {
            return $this->_routes[$scope];
        }
        $cache_id = $scope . '::' . $this->_cache_id;
        $cached_routes = $this->_cache->load($cache_id);
        if ($cached_routes) {
            $cached_routes = $this->get_serializer()->unserialize($cached_routes);
            if (is_array($cached_routes)) {
                $this->_routes[$scope] = $cached_routes;
                return $cached_routes;
            }
        }
        $routers = $this->_reader->read($scope);
        $routes = $routers[$this->_area_list->get_default_router($scope)]['routes'] ?? null;
        $routes_data = $this->get_serializer()->serialize($routes);
        $this->_cache->save($routes_data, $cache_id);
        $this->_routes[$scope] = $routes;
        return $routes;
    }
    /**
     * Retrieve route front name
     *
     * @param string $routeId
     * @param null|string $scope
     * @return string
     */
    public function get_route_front_name($route_id, $scope = null)
    {
        $routes = $this->_get_routes($scope);
        $route_id = $route_id ?? '';
        return isset($routes[$route_id]) ? $routes[$route_id]['frontName'] : $route_id;
    }
    /**
     * @inheritdoc
     *
     * @param string $frontName
     * @param string $scope
     * @return bool|int|string
     */
    public function get_route_by_front_name($front_name, $scope = null)
    {
        foreach ($this->_get_routes($scope) as $route_id => $route_data) {
            if ($route_data['frontName'] == $front_name) {
                return $route_id;
            }
        }
        return false;
    }
    /**
     * @inheritdoc
     *
     * @param string $frontName
     * @param string $scope
     * @return string[]
     */
    public function get_modules_by_front_name($front_name, $scope = null)
    {
        $routes = $this->_get_routes($scope);
        $modules = [];
        foreach ($routes as $route_data) {
            if ($route_data['frontName'] == $front_name && isset($route_data['modules'])) {
                $modules = $route_data['modules'];
                break;
            }
        }
        return array_unique($modules);
    }
    /**
     * Get serializer
     *
     * @return \Magento\Framework\Serialize\SerializerInterface
     */
    private function get_serializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\Object_Manager::get_instance()->get(Serializer_Interface::class);
        }
        return $this->serializer;
    }
}