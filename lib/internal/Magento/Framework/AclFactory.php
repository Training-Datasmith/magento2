<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

/**
 * Acl object factory
 *
 * @api
 */
class Acl_Factory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Create new magento acl instance
     *
     * @return \Magento\Framework\Acl
     */
    public function create()
    {
        return $this->_object_manager->create(Acl::class);
    }
}