<?php

/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Controller\Result;

/**
 * Factory class for @see \Magento\Framework\Controller\Result\Json
 *
 * @api
 */
class Json_Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager = null;
    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instance_name = null;
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\Controller\Result\Json::class)
    {
        $this->object_manager = $object_manager;
        $this->instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function create(array $data = [])
    {
        return $this->object_manager->create($this->instance_name, $data);
    }
}