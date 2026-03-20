<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl;

use Magento\Framework\Object_Manager_Interface;
/**
 * Factory for Acl resource
 *
 * @api
 */
class Acl_Resource_Factory
{
    public const RESOURCE_CLASS_NAME = \Magento\Framework\Acl\Acl_Resource::class;
    /**
     * @var ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Return new ACL resource model
     *
     * @param array $arguments
     * @return AclResource
     */
    public function create_resource(array $arguments = [])
    {
        return $this->_object_manager->create(self::RESOURCE_CLASS_NAME, $arguments);
    }
}