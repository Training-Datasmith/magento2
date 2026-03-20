<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache;

class Instance_Factory
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
     * Get cache instance model
     *
     * @param string $instanceName
     * @return \Magento\Framework\Cache\FrontendInterface
     * @throws \UnexpectedValueException
     */
    public function get($instance_name)
    {
        $instance = $this->_object_manager->get($instance_name);
        if (!$instance instanceof \Magento\Framework\Cache\Frontend_Interface) {
            throw new \UnexpectedValueException("Cache type class '{$instance_name}' has to be a cache frontend.");
        }
        return $instance;
    }
}