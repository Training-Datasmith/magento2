<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Grid\Render;

/**
 * Store render website
 */
class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Abstract_Renderer
{
    /**
     * @inheritDoc
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        return '<a title="' . __('Edit Web Site') . '"
            href="' . $this->get_url('adminhtml/*/editWebsite', ['website_id' => $row->get_website_id()]) . '">' . $this->escape_html($row->get_data($this->get_column()->get_index())) . '</a><br />' . '(' . __('Code') . ': ' . $row->get_code() . ')';
    }
}