<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Grid\Render;

/**
 * Store render store
 */
class Store extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @inheritDoc
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        if (!$row->get_data($this->get_column()->get_index())) {
            return null;
        }
        return '<a title="' . __('Edit Store View') . '"
            href="' . $this->get_url('adminhtml/*/editStore', ['store_id' => $row->get_store_id()]) . '">' . $this->escape_html($row->get_data($this->get_column()->get_index())) . '</a><br />' . '(' . __('Code') . ': ' . $row->get_store_code() . ')';
    }
}