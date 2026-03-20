<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/**
 * Range grid column filter
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * @api
 * @since 100.0.2
 */
class Range extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Abstract_Filter
{
    /**
     * Return formatted HTML
     *
     * @return string
     */
    public function get_html()
    {
        $html = '<div class="range"><div class="range-line">' . '<input type="text" name="' . $this->_get_html_name() . '[from]" id="' . $this->_get_html_id() . '_from" placeholder="' . __('From') . '" value="' . $this->get_escaped_value('from') . '" class="input-text admin__control-text no-changes" ' . $this->get_ui_id('filter', $this->_get_html_name(), 'from') . '/></div>';
        $html .= '<div class="range-line">' . '<input type="text" name="' . $this->_get_html_name() . '[to]" id="' . $this->_get_html_id() . '_to" placeholder="' . __('To') . '" value="' . $this->get_escaped_value('to') . '" class="input-text admin__control-text no-changes" ' . $this->get_ui_id('filter', $this->_get_html_name(), 'to') . '/></div></div>';
        return $html;
    }
    /**
     * Return the value at the specified index
     *
     * @param string|null $index
     * @return mixed
     */
    public function get_value($index = null)
    {
        if ($index) {
            return $this->get_data('value', $index);
        }
        $value = $this->get_data('value');
        if (isset($value['from']) && strlen($value['from']) > 0 || isset($value['to']) && strlen($value['to']) > 0) {
            return $value;
        }
        return null;
    }
    /**
     * @inheritDoc
     */
    public function get_condition()
    {
        $value = $this->get_value();
        return $value;
    }
}