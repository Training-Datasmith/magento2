<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Extension_Attribute;

/**
 * Factory class for @see
 * \Magento\Framework\Api\ExtensionAttribute\JoinDataInterface
 *
 * @api
 */
class Join_Data_Interface_Factory
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
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\Api\Extension_Attribute\Join_Data_Interface::class)
    {
        $this->_object_manager = $object_manager;
        $this->_instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\Api\ExtensionAttribute\JoinData
     */
    public function create(array $data = [])
    {
        return $this->_object_manager->create($this->_instance_name, $data);
    }
}