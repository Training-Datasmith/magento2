<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Introduced as a facade for presentation related operations.
 * Later replaced with Magento\Framework\View\Result component
 *
 * @api
 * @deprecated 101.0.0
 * @see \Magento\Framework\View\Result\Layout
 * @since 100.0.2
 */
interface View_Interface
{
    /**
     * Load layout updates
     *
     * @return ViewInterface
     */
    public function load_layout_updates();
    /**
     * Rendering layout
     *
     * @param   string $output
     * @return  ViewInterface
     */
    public function render_layout($output = '');
    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
     */
    public function get_default_layout_handle();
    /**
     * Load layout by handles(s)
     *
     * @param   string|null|bool $handles
     * @param   bool $generateBlocks
     * @param   bool $generateXml
     * @param   bool $addActionHandles
     * @return  ViewInterface
     * @throws  \RuntimeException
     */
    public function load_layout($handles = null, $generate_blocks = true, $generate_xml = true, $add_action_handles = true);
    /**
     * Generate layout xml
     *
     * @return ViewInterface
     */
    public function generate_layout_xml();
    /**
     * Add layout updates handles associated with the action page
     *
     * @param array $parameters page parameters
     * @param string $defaultHandle
     * @return bool
     */
    public function add_page_layout_handles(array $parameters = [], $default_handle = null);
    /**
     * Generate layout blocks
     *
     * @return ViewInterface
     */
    public function generate_layout_blocks();
    /**
     * Retrieve current page object
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function get_page();
    /**
     * Retrieve current layout object
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function get_layout();
    /**
     * Add layout handle by full controller action name
     *
     * @return ViewInterface
     */
    public function add_action_layout_handles();
    /**
     * Set isLayoutLoaded flag
     *
     * @param bool $value
     * @return void
     */
    public function set_is_layout_loaded($value);
    /**
     * Returns is layout loaded
     *
     * @return bool
     */
    public function is_layout_loaded();
}