<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Form multiline text elements
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
class Multiline extends Abstract_Element
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
        $this->set_line_count(2);
    }
    /**
     * Get HTML attributes
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['type', 'title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'maxlength', 'data-form-part', 'data-role', 'data-action'];
    }
    /**
     * Get label HTML
     *
     * @param int $suffix
     * @param string $scopeLabel
     *
     * @return string
     */
    public function get_label_html($suffix = 0, $scope_label = '')
    {
        $html = parent::get_label_html($suffix, $scope_label);
        return $html;
    }
    /**
     * Get element HTML
     *
     * @return string
     */
    public function get_element_html()
    {
        $html = '';
        $line_count = $this->get_line_count();
        for ($i = 0; $i < $line_count; $i++) {
            if ($i == 0 && $this->get_required()) {
                $this->set_class('input-text admin__control-text required-entry _required');
            } else {
                $this->set_class('input-text admin__control-text');
            }
            $html .= '<div class="multi-input admin__field-control"><input id="' . $this->get_html_id() . $i . '" name="' . $this->get_name() . '[' . $i . ']' . '" value="' . $this->get_escaped_value($i) . '" ' . $this->serialize($this->get_html_attributes()) . '  ' . $this->_get_ui_id($i) . '/>' . "\n";
            if ($i == 0) {
                $html .= $this->get_after_element_html();
            }
            $html .= '</div>';
        }
        return $html;
    }
    /**
     * Get default HTML
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function get_default_html()
    {
        $html = '';
        $line_count = $this->get_line_count();
        for ($i = 0; $i < $line_count; $i++) {
            $html .= $this->get_no_span() === true ? '' : '<span class="field-row">' . "\n";
            if ($i == 0) {
                $html .= '<label for="' . $this->get_html_id() . $i . '">' . $this->get_label() . ($this->get_required() ? ' <span class="required">*</span>' : '') . '</label>' . "\n";
                if ($this->get_required()) {
                    $this->set_class('input-text required-entry');
                }
            } else {
                $this->set_class('input-text');
                $html .= '<label>&nbsp;</label>' . "\n";
            }
            $html .= '<input id="' . $this->get_html_id() . $i . '" name="' . $this->get_name() . '[' . $i . ']' . '" value="' . $this->get_escaped_value($i) . '"' . $this->serialize($this->get_html_attributes()) . ' />' . "\n";
            if ($i == 0) {
                $html .= $this->get_after_element_html();
            }
            $html .= $this->get_no_span() === true ? '' : '</span>' . "\n";
        }
        return $html;
    }
    /**
     * @inheritDoc
     */
    public function get_escaped_value($index = null)
    {
        $value = $this->get_value();
        if (is_string($value)) {
            $value = explode("\n", $value);
            if (is_array($value)) {
                $this->set_value($value);
            }
        }
        return parent::get_escaped_value($index);
    }
}