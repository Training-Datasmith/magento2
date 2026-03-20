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
class Draggable_Handle extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * Render grid row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        return '<span class="' . $this->get_column()->get_inline_css() . '"></span>' . '<input type="hidden" name="entity_id" value="' . $row->get_data($this->get_column()->get_index()) . '"/>' . '<input type="hidden" name="position" value=""/>';
    }
}