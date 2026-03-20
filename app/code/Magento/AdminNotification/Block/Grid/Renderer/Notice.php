<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Block\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer;
use Magento\Framework\Data_Object;
/**
 * Renderer class for notice in the admin notifications grid
 */
class Notice extends Abstract_Renderer
{
    /**
     * Renders grid column
     */
    public function render(Data_Object $row): string
    {
        return '<span class="grid-row-title">' . $this->escape_html($row->get_title()) . '</span>' . ($row->get_description() ? '<br />' . $this->escape_html($row->get_description()) : '');
    }
}