<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\File;

/**
 * @api
 */
class Uploader_Factory
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
     * Create new uploader instance
     *
     * @param array $data
     * @return Uploader
     */
    public function create(array $data = [])
    {
        return $this->_object_manager->create(\Magento\Framework\File\Uploader::class, $data);
    }
}