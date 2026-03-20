<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp;

/**
 * Factory class for @see Config
 *
 * @api
 */
class Config_Factory
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
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = Config::class)
    {
        $this->_object_manager = $object_manager;
        $this->_instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Config
     */
    public function create(array $data = [])
    {
        return $this->_object_manager->create($this->_instance_name, $data);
    }
}