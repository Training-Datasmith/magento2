<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Router;

/**
 * Interface \Magento\Framework\App\Router\PathConfigInterface
 *
 * @api
 */
interface Path_Config_Interface
{
    /**
     * Retrieve secure url for current request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    public function get_current_secure_url(\Magento\Framework\App\Request_Interface $request);
    /**
     * Check whether given path should be secure according to configuration security requirements for URL
     * "Secure" should not be confused with https protocol, it is about web/secure/*_url settings usage only
     *
     * @param string $path
     * @return bool
     */
    public function should_be_secure($path);
    /**
     * Get router default request path
     *
     * @return string
     */
    public function get_default_path();
}