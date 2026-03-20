<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
/**
 * Form text element
 */
class Text extends Abstract_Element
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [])
    {
        parent::__construct($factory_element, $factory_collection, $escaper, $data);
        $this->set_type('text');
        $this->set_ext_type('textfield');
    }
    /**
     * Get the HTML
     *
     * @return mixed
     */
    public function get_html()
    {
        $this->add_class('input-text admin__control-text');
        return parent::get_html();
    }
    /**
     * Get the attributes
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['type', 'title', 'class', 'style', 'onclick', 'onchange', 'onkeyup', 'disabled', 'readonly', 'maxlength', 'tabindex', 'placeholder', 'data-form-part', 'data-role', 'data-validation-params', 'data-action'];
    }
}