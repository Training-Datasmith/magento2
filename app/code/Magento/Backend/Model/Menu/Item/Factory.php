<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Item;

/**
 * @api
 * @since 100.0.2
 */
class Factory
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
     * Create menu item from array
     *
     * @param array $data
     * @return \Magento\Backend\Model\Menu\Item
     */
    public function create(array $data = [])
    {
        return $this->_object_manager->create(\Magento\Backend\Model\Menu\Item::class, ['data' => $data]);
    }
}