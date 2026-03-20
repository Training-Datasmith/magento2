<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

/**
 * @api
 */
class Event_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * @param array $arguments
     * @return Event
     */
    public function create($arguments = [])
    {
        return $this->_object_manager->create(Event::class, $arguments);
    }
}