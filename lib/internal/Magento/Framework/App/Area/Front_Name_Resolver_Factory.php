<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Area;

/**
 * Application area front name resolver factory
 *
 * Since front-name resolver is a service, a Pool object would suit better than factory.
 * Keeping it for backward compatibility
 *
 * @api
 * @since 100.0.2
 */
class Front_Name_Resolver_Factory
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
     * Create front name resolver
     *
     * @param string $className
     * @return FrontNameResolverInterface
     */
    public function create($class_name)
    {
        return $this->_object_manager->create($class_name);
    }
}