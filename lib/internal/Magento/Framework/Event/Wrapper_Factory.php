<?php

declare (strict_types=1);
/**
 * Observer model factory
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event;

class Wrapper_Factory
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
     * Create wrapper instance
     *
     * @param array $arguments
     * @return \Magento\Framework\Event\Observer
     */
    public function create(array $arguments = [])
    {
        return $this->_object_manager->create(\Magento\Framework\Event\Observer::class, $arguments);
    }
}