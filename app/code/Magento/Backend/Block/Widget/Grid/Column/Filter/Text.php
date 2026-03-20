<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Text grid column filter
 *
 * @api
 * @since 100.0.2
 */
class Text extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Abstract_Filter
{
    /**
     * @inheritDoc
     */
    public function get_html()
    {
        $html = '<input type="text" name="' . $this->_get_html_name() . '" id="' . $this->_get_html_id() . '" value="' . $this->get_escaped_value() . '" class="input-text admin__control-text no-changes"' . $this->get_ui_id('filter', $this->_get_html_name()) . ' />';
        return $html;
    }
}