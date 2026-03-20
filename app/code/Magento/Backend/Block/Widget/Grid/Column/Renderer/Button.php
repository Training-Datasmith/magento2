<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * @api
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @since 100.0.2
 */
class Button extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * Render grid row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $button_type = $this->get_column()->get_button_type();
        $button_class = $this->get_column()->get_button_class();
        return '<button' . ($button_type ? ' type="' . $button_type . '"' : '') . ($button_class ? ' class="' . $button_class . '"' : '') . '>' . $this->get_column()->get_header() . '</button>';
    }
}