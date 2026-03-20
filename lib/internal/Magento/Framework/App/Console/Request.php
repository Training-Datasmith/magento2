<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Console;

class Request implements \Magento\Framework\App\Request_Interface
{
    /**
     * @var array
     */
    protected $params;
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $data = getopt('', $parameters);
        // It can happen that request comes from http (e.g. pub/cron.php), but it runs the console
        if ($data) {
            $this->set_params($data);
        } else {
            $this->set_params([]);
        }
    }
    /**
     * Retrieve module name
     *
     * @return void
     */
    public function get_module_name()
    {
        // phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired
        return;
    }
    /**
     * Set Module name
     *
     * @param string $name
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function set_module_name($name)
    {
    }
    /**
     * Retrieve action name
     *
     * @return void
     */
    public function get_action_name()
    {
        // phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired
        return;
    }
    /**
     * Set action name
     *
     * @param string $name
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function set_action_name($name)
    {
    }
    /**
     * Retrieve param by key
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get_param($key, $default_value = null)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        return $default_value;
    }
    /**
     * Retrieve all params as array
     *
     * @return array
     */
    public function get_params()
    {
        return $this->params;
    }
    /**
     * Set params from key value array
     *
     * @param array $data
     * @return $this
     */
    public function set_params(array $data)
    {
        $this->params = $data;
        return $this;
    }
    /**
     * Stub to satisfy RequestInterface
     *
     * @param null|string $name
     * @param null|string $default
     *
     * @return null|string|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_cookie($name, $default)
    {
    }
    /**
     * Stub to satisfy RequestInterface
     *
     * @return bool
     */
    public function is_secure()
    {
        return false;
    }
}