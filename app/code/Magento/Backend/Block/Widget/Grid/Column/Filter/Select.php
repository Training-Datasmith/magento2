<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Select grid column filter
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Select extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Abstract_Filter
{
    /**
     * {@inheritdoc}
     */
    protected function _get_options()
    {
        $empty_option = ['value' => null, 'label' => ''];
        $option_groups = $this->get_column()->get_option_groups();
        if ($option_groups) {
            array_unshift($option_groups, $empty_option);
            return $option_groups;
        }
        $col_options = $this->get_column()->get_options();
        if (!empty($col_options) && is_array($col_options)) {
            $options = [$empty_option];
            foreach ($col_options as $key => $option) {
                if (is_array($option)) {
                    $options[] = $option;
                } else {
                    $options[] = ['value' => $key, 'label' => $option];
                }
            }
            return $options;
        }
        return [];
    }
    /**
     * Render an option with selected value
     *
     * @param array $option
     * @param string $value
     * @return string
     */
    protected function _render_option($option, $value)
    {
        $selected = $option['value'] == $value && $value !== null ? ' selected="selected"' : '';
        return '<option value="' . $this->escape_html($option['value']) . '"' . $selected . '>' . $this->escape_html($option['label']) . '</option>';
    }
    /**
     * {@inheritdoc}
     */
    public function get_html()
    {
        $html = '<select name="' . $this->_get_html_name() . '" id="' . $this->_get_html_id() . '"' . $this->get_ui_id('filter', $this->_get_html_name()) . 'class="no-changes admin__control-select">';
        $value = $this->get_value();
        foreach ($this->_get_options() as $option) {
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $this->escape_html($option['label']) . '">';
                foreach ($option['value'] as $sub_option) {
                    $html .= $this->_render_option($sub_option, $value);
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_render_option($option, $value);
            }
        }
        $html .= '</select>';
        return $html;
    }
    /**
     * {@inheritdoc}
     */
    public function get_condition()
    {
        if ($this->get_value() === null) {
            return null;
        }
        return ['eq' => $this->get_value()];
    }
}