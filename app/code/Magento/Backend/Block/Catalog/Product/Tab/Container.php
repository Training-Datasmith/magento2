<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Catalog\Product\Tab;

/**
 * @api
 * @since 100.0.2
 */
class Container extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\Tab_Interface
{
    /**
     * Return Tab label
     *
     * @return string
     */
    public function get_tab_label()
    {
        return '';
    }
    /**
     * Return Tab title
     *
     * @return string
     */
    public function get_tab_title()
    {
        return $this->get_tab_label();
    }
    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function can_show_tab()
    {
        return true;
    }
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function is_hidden()
    {
        return false;
    }
}