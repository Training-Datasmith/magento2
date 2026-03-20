<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Form button element
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
class Button extends Abstract_Element
{
    /**
     * Additional html attributes
     *
     * @var string[]
     */
    protected $_html_attributes = ['data-mage-init'];
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [])
    {
        parent::__construct($factory_element, $factory_collection, $escaper, $data);
        $this->set_type('button');
        $this->set_ext_type('textfield');
    }
    /**
     * Html attributes
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        $attributes = parent::get_html_attributes();
        return array_merge($attributes, $this->_html_attributes);
    }
}