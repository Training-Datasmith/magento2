<?php

declare (strict_types=1);
/**
 * Application response factory
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

class Response_Factory
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
     * Create response
     *
     * @param array $arguments
     * @return ResponseInterface
     */
    public function create(array $arguments = [])
    {
        return $this->_object_manager->create(\Magento\Framework\App\Response_Interface::class, $arguments);
    }
}