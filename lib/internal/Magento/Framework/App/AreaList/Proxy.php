<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Area_List;

/**
 * Proxy for area list.
 */
class Proxy extends \Magento\Framework\App\Area_List implements \Magento\Framework\Object_Manager\Noninterceptable_Interface
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
     * @var \Magento\Framework\Locale\Resolver
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
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\App\Area_List::class, $shared = true)
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
     * @return \Magento\Framework\Locale\Resolver
     */
    protected function _get_subject()
    {
        if (!$this->_subject) {
            $this->_subject = true === $this->_is_shared ? $this->_object_manager->get($this->_instance_name) : $this->_object_manager->create($this->_instance_name);
        }
        return $this->_subject;
    }
    /**
     * Retrieve area code by front name
     *
     * @param string $frontName
     * @return null|string
     */
    public function get_code_by_front_name($front_name)
    {
        return $this->_get_subject()->get_code_by_front_name($front_name);
    }
    /**
     * Retrieve area front name by code
     *
     * @param string $areaCode
     * @return string
     */
    public function get_front_name($area_code)
    {
        return $this->_get_subject()->get_front_name($area_code);
    }
    /**
     * Retrieve area codes
     *
     * @return string[]
     */
    public function get_codes()
    {
        return $this->_get_subject()->get_codes();
    }
    /**
     * Retrieve default area router id
     *
     * @param string $areaCode
     * @return string
     */
    public function get_default_router($area_code)
    {
        return $this->_get_subject()->get_default_router($area_code);
    }
    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  \Magento\Framework\App\Area
     */
    public function get_area($code)
    {
        return $this->_get_subject()->get_area($code);
    }
}