<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
/**
 * Form select element
 */
class Checkboxes extends Abstract_Element
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
        $this->set_type('checkbox');
        $this->set_ext_type('checkboxes');
    }
    /**
     * Retrieve allow attributes
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['type', 'name', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled', 'data-role', 'data-action'];
    }
    /**
     * Prepare value list
     *
     * @return array
     */
    protected function _prepare_values()
    {
        $options = [];
        $values = [];
        if ($this->get_values()) {
            if (!is_array($this->get_values())) {
                $options = [$this->get_values()];
            } else {
                $options = $this->get_values();
            }
        } elseif ($this->get_options() && is_array($this->get_options())) {
            $options = $this->get_options();
        }
        foreach ($options as $k => $v) {
            if (is_array($v)) {
                if (isset($v['value'])) {
                    if (!isset($v['label'])) {
                        $v['label'] = $v['value'];
                    }
                    $values[] = ['label' => $v['label'], 'value' => $v['value']];
                }
            } else {
                $values[] = ['label' => $v, 'value' => $k];
            }
        }
        return $values;
    }
    /**
     * Retrieve HTML
     *
     * @return string
     */
    public function get_element_html()
    {
        $values = $this->_prepare_values();
        if (!$values) {
            return '';
        }
        $html = '<div class=nested>';
        foreach ($values as $value) {
            $html .= $this->_option_to_html($value);
        }
        $html .= '</div>' . $this->get_after_element_html();
        return $html;
    }
    /**
     * Was given value selected?
     *
     * @param string $value
     * @return string|null
     */
    public function get_checked($value)
    {
        $checked = $this->get_value() ?? $this->get_data('checked');
        if (!$checked) {
            return null;
        }
        if (!is_array($checked)) {
            $checked = [(string) $checked];
        } else {
            foreach ($checked as $k => $v) {
                $checked[$k] = (string) $v;
            }
        }
        if (in_array((string) $value, $checked)) {
            return 'checked';
        }
        return null;
    }
    /**
     * Was value disabled for selection?
     *
     * @param string $value
     * @return string|null
     */
    public function get_disabled($value)
    {
        if ($disabled = $this->get_data('disabled')) {
            if (!is_array($disabled)) {
                $disabled = [(string) $disabled];
            } else {
                foreach ($disabled as $k => $v) {
                    $disabled[$k] = (string) $v;
                }
            }
            if (in_array((string) $value, $disabled)) {
                return 'disabled';
            }
        }
        return null;
    }
    /**
     * Get onclick event handler.
     *
     * @param string $value
     * @return string|null
     */
    public function get_onclick($value = '$value')
    {
        if ($onclick = $this->get_data('onclick')) {
            return str_replace('$value', $value, $onclick);
        }
        return null;
    }
    /**
     * Get onchange event handler.
     *
     * @param string $value
     * @return string|null
     */
    public function get_onchange($value = '$value')
    {
        if ($onchange = $this->get_data('onchange')) {
            return str_replace('$value', $value, $onchange);
        }
        return null;
    }
    /**
     * Render a checkbox.
     *
     * @param array $option
     * @return string
     */
    protected function _option_to_html($option)
    {
        $id = $this->get_html_id() . '_' . $this->_escape($option['value']);
        $html = '<div class="field choice admin__field admin__field-option"><input id="' . $id . '"';
        foreach ($this->get_html_attributes() as $attribute) {
            if ($value = $this->get_data_using_method($attribute, $option['value'])) {
                $html .= ' ' . $attribute . '="' . $value . '" class="admin__control-checkbox"';
            }
        }
        $html .= ' value="' . $option['value'] . '" />' . ' <label for="' . $id . '" class="admin__field-label"><span>' . $option['label'] . '</span></label></div>' . "\n";
        return $html;
    }
}