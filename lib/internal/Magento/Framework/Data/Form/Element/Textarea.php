<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
/**
 * Form textarea element.
 */
class Textarea extends Abstract_Element
{
    /**
     * Default number of rows
     */
    public const DEFAULT_ROWS = 2;
    /**
     * Default number of columns
     */
    public const DEFAULT_COLS = 15;
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [])
    {
        parent::__construct($factory_element, $factory_collection, $escaper, $data);
        $this->set_type('textarea');
        $this->set_ext_type('textarea');
        if (!$this->get_rows()) {
            $this->set_rows(self::DEFAULT_ROWS);
        }
        if (!$this->get_cols()) {
            $this->set_cols(self::DEFAULT_COLS);
        }
    }
    /**
     * Return the HTML attributes
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['title', 'class', 'style', 'onclick', 'onchange', 'rows', 'cols', 'readonly', 'maxlength', 'disabled', 'onkeyup', 'tabindex', 'data-form-part', 'data-role', 'data-action'];
    }
    /**
     * Return the element as HTML
     *
     * @return string
     */
    public function get_element_html()
    {
        $this->add_class('textarea admin__control-textarea');
        $html = '<textarea id="' . $this->get_html_id() . '" name="' . $this->get_name() . '" ' . $this->serialize($this->get_html_attributes()) . $this->_get_ui_id() . ' >';
        $html .= $this->get_escaped_value();
        $html .= '</textarea>';
        $html .= $this->get_after_element_html();
        return $html;
    }
}