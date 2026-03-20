<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Http;

use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Config\Config_Options_List_Constants;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Context data for requests
 *
 * @api
 */
class Context implements Reset_After_Request_Interface
{
    /**
     * Currency cache context
     */
    public const CONTEXT_CURRENCY = 'current_currency';
    /**
     * Data storage
     *
     * @var array
     */
    protected $data = [];
    /**
     * @var array
     */
    protected $default = [];
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var DeploymentConfig|null
     */
    private ?Deployment_Config $deployment_config = null;
    /**
     * @param array $data
     * @param array $default
     * @param Json|null $serializer
     */
    public function __construct(array $data = [], array $default = [], ?Json $serializer = null)
    {
        $this->data = $data;
        $this->default = $default;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Json::class);
    }
    /**
     * Data setter
     *
     * @param string $name
     * @param mixed $value
     * @param mixed $default
     * @return \Magento\Framework\App\Http\Context
     */
    public function set_value($name, $value, $default)
    {
        if ($default !== null) {
            $this->default[$name] = $default;
        }
        $this->data[$name] = $value;
        return $this;
    }
    /**
     * Unset data from vary array
     *
     * @param string $name
     * @return null
     */
    public function uns_value($name)
    {
        unset($this->data[$name]);
        return $this;
    }
    /**
     * Data getter
     *
     * @param string $name
     * @return mixed|null
     */
    public function get_value($name)
    {
        return $this->data[$name] ?? $this->default[$name] ?? null;
    }
    /**
     * Return all data
     *
     * @return array
     */
    public function get_data()
    {
        $data = [];
        foreach ($this->data as $name => $value) {
            if ($value && $value != $this->default[$name]) {
                $data[$name] = $value;
            }
        }
        return $data;
    }
    /**
     * Return vary string to be used as a part of page cache identifier
     *
     * @return string|null
     */
    public function get_vary_string()
    {
        $data = $this->get_data();
        if (!empty($data)) {
            $salt = (string) $this->get_deployment_config()->get(Config_Options_List_Constants::CONFIG_PATH_CRYPT_KEY);
            ksort($data);
            return hash('sha256', $this->serializer->serialize($data) . '|' . $salt);
        }
        return null;
    }
    /**
     * Get data and default data in "key-value" format
     *
     * @return array
     */
    public function to_array()
    {
        return ['data' => $this->data, 'default' => $this->default];
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->data = [];
        $this->default = [];
    }
    /**
     * Get DeploymentConfig
     *
     * @return DeploymentConfig
     */
    private function get_deployment_config(): Deployment_Config
    {
        if ($this->deployment_config === null) {
            $this->deployment_config = Object_Manager::get_instance()->get(Deployment_Config::class);
        }
        return $this->deployment_config;
    }
}