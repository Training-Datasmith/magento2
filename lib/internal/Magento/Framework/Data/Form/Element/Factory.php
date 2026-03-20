<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Object_Manager_Interface;
/**
 * Form element Factory
 *
 * @api
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * Standard library element types
     *
     * @var string[]
     */
    protected $_standard_types = ['button', 'checkbox', 'checkboxes', 'column', 'date', 'editablemultiselect', 'editor', 'fieldset', 'file', 'gallery', 'hidden', 'image', 'imagefile', 'label', 'link', 'multiline', 'multiselect', 'note', 'obscure', 'password', 'radio', 'radios', 'reset', 'select', 'submit', 'text', 'textarea', 'time'];
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Factory method
     *
     * @param string $elementType Standard element type or Custom element class
     * @param array $config
     * @return AbstractElement
     * @throws \InvalidArgumentException
     */
    public function create($element_type, array $config = [])
    {
        if (in_array($element_type, $this->_standard_types)) {
            $class_name = 'Magento\Framework\Data\Form\Element\\' . ucfirst($element_type);
        } else {
            $class_name = $element_type;
        }
        $element = $this->_object_manager->create($class_name, $config);
        if (!$element instanceof Abstract_Element) {
            throw new \InvalidArgumentException($class_name . ' doesn\'t extend \Magento\Framework\Data\Form\Element\AbstractElement');
        }
        return $element;
    }
}