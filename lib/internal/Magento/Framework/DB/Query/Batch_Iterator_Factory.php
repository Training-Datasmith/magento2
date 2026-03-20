<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Query;

/**
 * Factory class for @see \Magento\Framework\DB\Query\BatchIterator
 */
class Batch_Iterator_Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $object_manager = null;
    /**
     * Instance name to create
     *
     * @var string
     */
    private $instance_name = null;
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\DB\Query\Batch_Iterator::class)
    {
        $this->object_manager = $object_manager;
        $this->instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\DB\Query\BatchIteratorInterface
     */
    public function create(array $data = [])
    {
        return $this->object_manager->create($this->instance_name, $data);
    }
}