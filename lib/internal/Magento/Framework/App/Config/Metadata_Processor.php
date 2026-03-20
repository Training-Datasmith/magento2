<?php

declare (strict_types=1);
/**
 * Configuration metadata processor
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

class Metadata_Processor
{
    /**
     * @var \Magento\Framework\App\Config\Data\ProcessorFactory
     */
    protected $_processor_factory;
    /**
     * @var array
     */
    protected $_metadata = [];
    /**
     * @param \Magento\Framework\App\Config\Data\ProcessorFactory $processorFactory
     * @param Initial $initialConfig
     */
    public function __construct(\Magento\Framework\App\Config\Data\Processor_Factory $processor_factory, Initial $initial_config)
    {
        $this->_processor_factory = $processor_factory;
        $this->_metadata = $initial_config->get_metadata();
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
     * Process config data
     *
     * @param array $data
     * @return array
     */
    public function process(array $data)
    {
        foreach ($this->_metadata as $path => $metadata) {
            /** @var \Magento\Framework\App\Config\Data\ProcessorInterface $processor */
            $processor = $this->_processor_factory->get($metadata['backendModel']);
            $value = $processor->process_value($this->_get_value($data, $path));
            $this->_set_value($data, $path, $value);
        }
        return $data;
    }
}