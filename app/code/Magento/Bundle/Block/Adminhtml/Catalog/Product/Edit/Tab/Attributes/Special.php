<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes;

/**
 * Bundle Special Price Attribute Block
 */
class Special extends \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
    /**
     * Return the HTML for this element
     *
     * @return string
     */
    public function get_element_html()
    {
        $html = '<input id="' . $this->get_element()->get_html_id() . '" name="' . $this->get_element()->get_name() . '" value="' . $this->get_element()->get_escaped_value() . '" ' . $this->get_element()->serialize($this->get_element()->get_html_attributes()) . '/>' . "\n" . '<label class="addafter" for="' . $this->get_element()->get_html_id() . '"><strong>[%]</strong></label>';
        return $html;
    }
}