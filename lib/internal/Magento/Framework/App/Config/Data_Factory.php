<?php

declare (strict_types=1);
/**
 * Data Factory.
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

class Data_Factory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instance_name = null;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\App\Config\Data::class)
    {
        $this->_object_manager = $object_manager;
        $this->_instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return DataInterface
     */
    public function create(array $data = [])
    {
        return $this->_object_manager->create($this->_instance_name, $data);
    }
}