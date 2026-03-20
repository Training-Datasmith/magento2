<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Grid\Render;

use Magento\Framework\Data_Object;
/**
 * Store render group
 */
class Group extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @inheritDoc
     */
    public function render(Data_Object $row)
    {
        if (!$row->get_data($this->get_column()->get_index())) {
            return null;
        }
        return '<a title="' . __('Edit Store') . '"
            href="' . $this->get_url('adminhtml/*/editGroup', ['group_id' => $row->get_group_id()]) . '">' . $this->escape_html($row->get_data($this->get_column()->get_index())) . '</a><br />' . '(' . __('Code') . ': ' . $this->escape_html($row->get_group_code()) . ')';
    }
}