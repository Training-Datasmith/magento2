<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Data\Form;
use Magento\Framework\Escaper;
/**
 * Form fieldset
 *
 * @api
 * @since 100.0.2
 */
class Fieldset extends Abstract_Element
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
        $this->_renderer = Form::get_fieldset_renderer();
        $this->set_type('fieldset');
        if (isset($data['advancedSection'])) {
            $this->set_advanced_label($data['advancedSection']);
        }
    }
    /**
     * Get elements html
     *
     * @return string
     */
    public function get_element_html()
    {
        $html = $this->get_before_element_html();
        $html .= '<fieldset area-hidden="false" id="' . $this->get_html_id() . '"' . $this->serialize(['class']) . $this->_get_ui_id() . '>' . "\n";
        if ($this->get_legend()) {
            $html .= '<legend ' . $this->_get_ui_id('legend') . '>' . $this->get_legend() . '</legend>' . "\n";
        }
        $html .= $this->get_children_html();
        $html .= '</fieldset>' . "\n";
        $html .= $this->get_after_element_html();
        return $html;
    }
    /**
     * Get Children element's array
     *
     * @return AbstractElement[]
     */
    public function get_children()
    {
        $elements = [];
        foreach ($this->get_elements() as $element) {
            if ($element->get_type() != 'fieldset') {
                $elements[] = $element;
            }
        }
        return $elements;
    }
    /**
     * Get Children element's html
     *
     * @return string
     */
    public function get_children_html()
    {
        return $this->_elements_to_html($this->get_children());
    }
    /**
     * Get Basic elements' array
     *
     * @return AbstractElement[]
     */
    public function get_basic_children()
    {
        $elements = [];
        foreach ($this->get_elements() as $element) {
            if (!$element->is_advanced()) {
                $elements[] = $element;
            }
        }
        return $elements;
    }
    /**
     * Get Basic elements' html in sorted order
     *
     * @return string
     */
    public function get_basic_children_html()
    {
        return $this->_elements_to_html($this->get_basic_children());
    }
    /**
     * Get Number of Basic Children
     *
     * @return int
     */
    public function get_count_basic_children()
    {
        return count($this->get_basic_children());
    }
    /**
     * Get Advanced elements'
     *
     * @return array
     */
    public function get_advanced_children()
    {
        $elements = [];
        foreach ($this->get_elements() as $element) {
            if ($element->is_advanced()) {
                $elements[] = $element;
            }
        }
        return $elements;
    }
    /**
     * Get Advanced elements' html in sorted order
     *
     * @return string
     */
    public function get_advanced_children_html()
    {
        return $this->_elements_to_html($this->get_advanced_children());
    }
    /**
     * Whether fieldset contains advance section
     *
     * @return bool
     */
    public function has_advanced()
    {
        foreach ($this->get_elements() as $element) {
            if ($element->is_advanced()) {
                return true;
            }
        }
        return false;
    }
    /**
     * Get SubFieldset
     *
     * @return AbstractElement[]
     */
    public function get_sub_fieldset()
    {
        $elements = [];
        foreach ($this->get_elements() as $element) {
            if ($element->get_type() == 'fieldset' && !$element->is_advanced()) {
                $elements[] = $element;
            }
        }
        return $elements;
    }
    /**
     * Enter description here...
     *
     * @return string
     */
    public function get_sub_fieldset_html()
    {
        return $this->_elements_to_html($this->get_sub_fieldset());
    }
    /**
     * Enter description here...
     *
     * @return string
     */
    public function get_default_html()
    {
        $html = '<div><h4 class="icon-head head-edit-form fieldset-legend">' . $this->get_legend() . '</h4>' . "\n";
        $html .= $this->get_element_html();
        $html .= '</div>';
        return $html;
    }
    /**
     * Add field to fieldset
     *
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param bool $after
     * @param bool $isAdvanced
     * @return AbstractElement
     */
    public function add_field($element_id, $type, $config, $after = false, $is_advanced = false)
    {
        $element = parent::add_field($element_id, $type, $config, $after);
        if ($renderer = Form::get_fieldset_element_renderer()) {
            $element->set_renderer($renderer);
        }
        $element->set_advanced($is_advanced);
        return $element;
    }
    /**
     * Return elements as html string
     *
     * @param AbstractElement[] $elements
     * @return string
     */
    protected function _elements_to_html($elements)
    {
        $html = '';
        foreach ($elements as $element) {
            $html .= $element->to_html();
        }
        return $html;
    }
}