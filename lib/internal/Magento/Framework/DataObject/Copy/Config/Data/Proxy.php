<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data_Object\Copy\Config\Data;

/**
 * Proxy class for @see \Magento\Framework\DataObject\Copy\Config\Data
 */
class Proxy extends \Magento\Framework\Data_Object\Copy\Config\Data implements \Magento\Framework\Object_Manager\Noninterceptable_Interface
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
     * @var \Magento\Framework\DataObject\Copy\Config\Data
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
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\Data_Object\Copy\Config\Data::class, $shared = true)
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
     * @return \Magento\Framework\DataObject\Copy\Config\Data
     */
    protected function _get_subject()
    {
        if (!$this->_subject) {
            $this->_subject = true === $this->_is_shared ? $this->_object_manager->get($this->_instance_name) : $this->_object_manager->create($this->_instance_name);
        }
        return $this->_subject;
    }
    /**
     * @inheritDoc
     */
    public function merge(array $config)
    {
        return $this->_get_subject()->merge($config);
    }
    /**
     * @inheritDoc
     */
    public function get($path = null, $default = null)
    {
        return $this->_get_subject()->get($path, $default);
    }
    /**
     * @inheritDoc
     */
    public function reset()
    {
        return $this->_get_subject()->reset();
    }
}