<?php

declare (strict_types=1);
/**
 * Configuration metadata processor
 *
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config\Data\Processor_Factory;
use Magento\Framework\App\Config\Spi\Post_Processor_Interface;
use Magento\Framework\App\Object_Manager;
/**
 * Post-process config values using their backend models.
 */
class Metadata_Config_Type_Processor implements Post_Processor_Interface
{
    /**
     * @var ProcessorFactory
     */
    protected $_processor_factory;
    /**
     * @var array
     */
    protected $_metadata = [];
    /**
     * Source of configurations
     *
     * @var ConfigSourceInterface
     */
    private $config_source;
    /**
     * The resolver for configuration paths
     *
     * @var ConfigPathResolver
     */
    private $config_path_resolver;
    /**
     * @param ProcessorFactory $processorFactory
     * @param Initial $initialConfig
     * @param ConfigSourceInterface $configSource Source of configurations
     * @param ConfigPathResolver $configPathResolver The resolver for configuration paths
     */
    public function __construct(Processor_Factory $processor_factory, Initial $initial_config, ?Config_Source_Interface $config_source = null, ?Config_Path_Resolver $config_path_resolver = null)
    {
        $this->_processor_factory = $processor_factory;
        $this->_metadata = $initial_config->get_metadata();
        $this->config_source = $config_source ?: Object_Manager::get_instance()->get(Config_Source_Interface::class);
        $this->config_path_resolver = $config_path_resolver ?: Object_Manager::get_instance()->get(Config_Path_Resolver::class);
    }
    /**
     * Retrieve array value by path
     *
     * @param array $data
     * @param string $path
     * @return string|null
     */
    protected function _get_value(array $data, $path)
    {
        $keys = explode('/', $path);
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return null;
            }
        }
        return $data;
    }
    /**
     * Set array value by path
     *
     * @param array &$container
     * @param string $path
     * @param string $value
     * @return void
     */
    protected function _set_value(array &$container, $path, $value)
    {
        $segments = explode('/', $path);
        $current_pointer =& $container;
        foreach ($segments as $segment) {
            if (!isset($current_pointer[$segment])) {
                $current_pointer[$segment] = [];
            }
            $current_pointer =& $current_pointer[$segment];
        }
        $current_pointer = $value;
    }
    /**
     * Process data by sections: stores, default, websites and by scope codes.
     *
     * Doesn't processes configuration values that present in $_ENV variables.
     *
     * @param array $data An array of scope configuration
     * @param string $scope The configuration scope
     * @param string|null $scopeCode The configuration scope code
     * @return array An array of processed configuration
     */
    private function process_scope_data(array $data, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_code = null)
    {
        foreach ($this->_metadata as $path => $metadata) {
            try {
                $config_path = $this->config_path_resolver->resolve($path, $scope, $scope_code);
                if (!empty($this->config_source->get($config_path))) {
                    continue;
                }
            } catch (\Throwable $exception) {
                //Failed to load scopes or config source, perhaps config data received is outdated.
                return $data;
            }
            if (isset($metadata['backendModel'])) {
                /** @var \Magento\Framework\App\Config\Data\ProcessorInterface $processor */
                $processor = $this->_processor_factory->get($metadata['backendModel']);
                $value = $processor->process_value($this->_get_value($data, $path));
                $this->_set_value($data, $path, $value);
            }
        }
        return $data;
    }
    /**
     * Process config data
     *
     * @param array $rawData An array of configuration
     * @return array
     */
    public function process(array $raw_data)
    {
        $processed_data = [];
        foreach ($raw_data as $scope => $scope_data) {
            if ($scope == Scope_Config_Interface::SCOPE_TYPE_DEFAULT) {
                $processed_data[Scope_Config_Interface::SCOPE_TYPE_DEFAULT] = $this->process_scope_data($scope_data);
            } else {
                foreach ($scope_data as $scope_code => $data) {
                    $processed_data[$scope][$scope_code] = $this->process_scope_data($data, $scope, $scope_code);
                }
            }
        }
        return $processed_data;
    }
}