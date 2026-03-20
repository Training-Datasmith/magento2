<?php

declare (strict_types=1);
/**
 * Application request factory
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

class Request_Factory
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
     * Create request
     *
     * @param array $arguments
     * @return RequestInterface
     */
    public function create(array $arguments = [])
    {
        return $this->object_manager->create(\Magento\Framework\App\Request_Interface::class, $arguments);
    }
}