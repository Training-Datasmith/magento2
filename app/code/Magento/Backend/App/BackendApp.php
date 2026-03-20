<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App;

/**
 * Backend Application which uses Magento Backend authentication process
 * @api
 * @since 100.0.2
 */
class Backend_App
{
    private $cookie_path;
    private $startup_page;
    private $acl_resource_name;
    /**
     * @param string $cookiePath
     * @param string $startupPage
     * @param string $aclResourceName
     */
    public function __construct($cookie_path, $startup_page, $acl_resource_name)
    {
        $this->cookie_path = $cookie_path;
        $this->startup_page = $startup_page;
        $this->acl_resource_name = $acl_resource_name;
    }
    /**
     * Cookie path for the application to set cookie to
     *
     * @return string
     */
    public function get_cookie_path()
    {
        return $this->cookie_path;
    }
    /**
     * Startup Page of the application to redirect after login
     *
     * @return string
     */
    public function get_startup_page()
    {
        return $this->startup_page;
    }
    /**
     * ACL resource name to authorize access to
     *
     * @return string
     */
    public function get_acl_resource()
    {
        return $this->acl_resource_name;
    }
}