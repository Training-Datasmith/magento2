<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Tab;

/**
 * Backend Widget Tab Interface
 *
 * @api
 * @since 100.0.2
 */
interface Tab_Interface
{
    /**
     * Return Tab label
     *
     * @return string
     */
    public function get_tab_label();
    /**
     * Return Tab title
     *
     * @return string
     */
    public function get_tab_title();
    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function can_show_tab();
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function is_hidden();
}