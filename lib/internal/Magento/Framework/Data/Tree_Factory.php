<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

use Magento\Framework\Object_Manager_Interface;
/**
 * Factory class for @see \Magento\Framework\Data\Tree
 *
 * @api
 */
class Tree_Factory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
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
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\Data\Tree::class)
    {
        $this->object_manager = $object_manager;
        $this->instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\Data\Tree
     */
    public function create(array $data = [])
    {
        return $this->object_manager->create($this->instance_name, $data);
    }
}