<?php

declare (strict_types=1);
/**
 * Application request
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * @api
 * @since 100.0.2
 */
interface Request_Interface
{
    /**
     * Retrieve module name
     *
     * @return string
     */
    public function get_module_name();
    /**
     * Set Module name
     *
     * @param string $name
     * @return $this
     */
    public function set_module_name($name);
    /**
     * Retrieve action name
     *
     * @return string
     */
    public function get_action_name();
    /**
     * Set action name
     *
     * @param string $name
     * @return $this
     */
    public function set_action_name($name);
    /**
     * Retrieve param by key
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get_param($key, $default_value = null);
    /**
     * Set params from key value array
     *
     * @param array $params
     * @return $this
     */
    public function set_params(array $params);
    /**
     * Retrieve all params as array
     *
     * @return array
     */
    public function get_params();
    /**
     * Retrieve cookie value
     *
     * @param string|null $name
     * @param string|null $default
     * @return string|null
     */
    public function get_cookie($name, $default);
    /**
     * Returns whether request was delivered over HTTPS
     *
     * @return bool
     */
    public function is_secure();
}