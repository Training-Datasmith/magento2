<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Input extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @var array
     */
    protected $_values;
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $html = '<input type="text" ';
        $html .= 'name="' . $this->get_column()->get_id() . '" ';
        $html .= 'value="' . $row->get_data($this->get_column()->get_index()) . '"';
        $html .= 'class="input-text ' . $this->get_column()->get_inline_css() . '"/>';
        return $html;
    }
}