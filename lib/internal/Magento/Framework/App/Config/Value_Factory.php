<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Config;

/**
 * Factory class
 *
 * @api
 */
class Value_Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager = null;
    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instance_name = null;
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\App\Config\Value_Interface::class)
    {
        $this->_object_manager = $object_manager;
        $this->_instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\App\Config\ValueInterface
     * @throws \InvalidArgumentException
     */
    public function create(array $data = [])
    {
        $model = $this->_object_manager->create($this->_instance_name, $data);
        if (!$model instanceof \Magento\Framework\App\Config\Value_Interface) {
            throw new \InvalidArgumentException('Invalid config field model: ' . $this->_instance_name);
        }
        return $model;
    }
}