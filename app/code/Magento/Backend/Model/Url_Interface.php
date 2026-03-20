<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model;

/**
 * @api
 * @since 100.0.2
 */
interface Url_Interface extends \Magento\Framework\Url_Interface
{
    /**
     * Secret key query param name
     */
    public const SECRET_KEY_PARAM_NAME = 'key';
    /**
     * xpath to startup page in configuration
     */
    public const XML_PATH_STARTUP_MENU_ITEM = 'admin/startup/menu_item_id';
    /**
     * Generate secret key for controller and action based on form key
     *
     * @param string $routeName
     * @param string $controller Controller name
     * @param string $action Action name
     * @return string
     */
    public function get_secret_key($route_name = null, $controller = null, $action = null);
    /**
     * Return secret key settings flag
     *
     * @return bool
     */
    public function use_secret_key();
    /**
     * Enable secret key using
     *
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function turn_on_secret_key();
    /**
     * Disable secret key using
     *
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function turn_off_secret_key();
    /**
     * Refresh admin menu cache etc.
     *
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function renew_secret_urls();
    /**
     * Find admin start page url
     *
     * @return string
     */
    public function get_startup_page_url();
    /**
     * Set custom auth session
     *
     * @param \Magento\Backend\Model\Auth\Session $session
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function set_session(\Magento\Backend\Model\Auth\Session $session);
    /**
     * Return backend area front name, defined in configuration
     *
     * @return string
     */
    public function get_area_front_name();
    /**
     * Find first menu item that user is able to access
     *
     * @return string
     */
    public function find_first_available_menu();
}