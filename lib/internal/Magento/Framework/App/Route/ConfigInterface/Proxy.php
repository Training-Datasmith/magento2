<?php

declare (strict_types=1);
/**
 * Routes configuration model proxy
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Route\Config_Interface;

/**
 * Proxy class for \Magento\Framework\App\ResourceConnection
 */
class Proxy implements \Magento\Framework\App\Route\Config_Interface, \Magento\Framework\Object_Manager\Noninterceptable_Interface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager = null;
    /**
     * Proxied instance name
     *
     * @var string
     */
    protected $_instance_name = null;
    /**
     * Proxied instance
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_subject = null;
    /**
     * Instance shareability flag
     *
     * @var bool
     */
    protected $_is_shared = null;
    /**
     * Proxy constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\App\Route\Config_Interface::class, $shared = true)
    {
        $this->_object_manager = $object_manager;
        $this->_instance_name = $instance_name;
        $this->_is_shared = $shared;
    }
    /**
     * Remove links to other objects.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['_subject', '_isShared'];
    }
    /**
     * Retrieve ObjectManager from global scope
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_object_manager = \Magento\Framework\App\Object_Manager::get_instance();
    }
    /**
     * Clone proxied instance
     *
     * @return void
     */
    public function __clone()
    {
        $this->_subject = clone $this->_get_subject();
    }
    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\App\Route\ConfigInterface
     */
    protected function _get_subject()
    {
        if (!$this->_subject) {
            $this->_subject = true === $this->_is_shared ? $this->_object_manager->get($this->_instance_name) : $this->_object_manager->create($this->_instance_name);
        }
        return $this->_subject;
    }
    /**
     * Retrieve route front name
     *
     * @param string $routeId
     * @param string $scope
     * @return string
     */
    public function get_route_front_name($route_id, $scope = null)
    {
        return $this->_get_subject()->get_route_front_name($route_id, $scope);
    }
    /**
     * Get route id by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return string
     */
    public function get_route_by_front_name($front_name, $scope = null)
    {
        return $this->_get_subject()->get_route_by_front_name($front_name, $scope);
    }
    /**
     * Retrieve list of modules by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return array
     */
    public function get_modules_by_front_name($front_name, $scope = null)
    {
        $this->_get_subject()->get_modules_by_front_name($front_name, $scope);
    }
}