<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form;

class Element_Factory
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
     * Create Magento data form with provided params
     *
     * @param string $elementClass
     * @param array $data
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function create($element_class, array $data = [])
    {
        return $this->_object_manager->create($element_class, ['data' => $data]);
    }
}