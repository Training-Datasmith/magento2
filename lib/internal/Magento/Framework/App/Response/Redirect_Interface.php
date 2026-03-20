<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Response;

/**
 * Interface \Magento\Framework\App\Response\RedirectInterface
 *
 * @api
 */
interface Redirect_Interface
{
    public const PARAM_NAME_REFERER_URL = 'referer_url';
    public const PARAM_NAME_ERROR_URL = 'error_url';
    public const PARAM_NAME_SUCCESS_URL = 'success_url';
    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    public function get_referer_url();
    /**
     * Set referer url for redirect in response
     *
     * @param   string $defaultUrl
     * @return  string
     */
    public function get_redirect_url($default_url = null);
    /**
     * Redirect to error page
     *
     * @param string $defaultUrl
     * @return  string
     */
    public function error($default_url);
    /**
     * Redirect to success page
     *
     * @param string $defaultUrl
     * @return string
     */
    public function success($default_url);
    /**
     * Update path params for url builder
     *
     * @param array $arguments
     * @return array
     */
    public function update_path_params(array $arguments);
    /**
     * Set redirect into response
     *
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param string $path
     * @param array $arguments
     * @return void
     */
    public function redirect(\Magento\Framework\App\Response_Interface $response, $path, $arguments = []);
}