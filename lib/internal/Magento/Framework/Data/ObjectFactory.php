<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

/**
 * Class ObjectFactory
 * @package Magento\Framework\Data
 */
class Object_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create data object
     *
     * @param string $className
     * @param array $arguments
     * @return \Magento\Framework\DataObject
     */
    public function create($class_name, array $arguments)
    {
        return $this->object_manager->create($class_name, $arguments);
    }
}