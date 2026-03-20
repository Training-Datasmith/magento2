<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Object_Manager_Interface;
class Collection_Factory
{
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
     * Create collection factory with specified parameters
     *
     * @param array $data
     * @return Collection
     */
    public function create(array $data = [])
    {
        return $this->_object_manager->create(\Magento\Framework\Data\Form\Element\Collection::class, $data);
    }
}