<?php

declare (strict_types=1);
/**
 * Base config model factory
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

class Base_Factory
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
     * Create config model
     *
     * @param string|\Magento\Framework\Simplexml\Element $sourceData
     * @return \Magento\Framework\App\Config\Base
     */
    public function create($source_data = null)
    {
        return $this->_object_manager->create(\Magento\Framework\App\Config\Base::class, ['sourceData' => $source_data]);
    }
}