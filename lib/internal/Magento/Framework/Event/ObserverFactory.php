<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event;

/**
 * Observer model factory
 *
 * @api
 */
class Observer_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Get observer model instance
     *
     * @param string $className
     * @return mixed
     */
    public function get($class_name)
    {
        return $this->_object_manager->get($class_name);
    }
    /**
     * Create observer model instance
     *
     * @param string $className
     * @param array $arguments
     * @return mixed
     */
    public function create($class_name, array $arguments = [])
    {
        return $this->_object_manager->create($class_name, $arguments);
    }
}