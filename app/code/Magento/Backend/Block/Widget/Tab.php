<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

use Magento\Backend\Block\Widget\Tab\Tab_Interface;
/**
 * @api
 * @since 100.0.2
 */
class Tab extends \Magento\Backend\Block\Template implements Tab_Interface
{
    /**
     * Return Tab label
     *
     * @return string
     */
    public function get_tab_label()
    {
        return $this->get_label();
    }
    /**
     * Return Tab title
     *
     * @return string
     */
    public function get_tab_title()
    {
        return $this->get_title();
    }
    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function can_show_tab()
    {
        return $this->has_can_show() ? (bool) $this->get_can_show() : true;
    }
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function is_hidden()
    {
        return $this->has_is_hidden() ? (bool) $this->get_is_hidden() : false;
    }
    /**
     * @return string
     */
    public function get_tab_class()
    {
        return $this->get_class();
    }
    /**
     * @return string
     */
    public function get_tab_url()
    {
        return $this->has_data('url') ? $this->get_data('url') : '#';
    }
}