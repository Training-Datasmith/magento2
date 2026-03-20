<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

/**
 * Configuration data container
 */
class Data implements Data_Interface
{
    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];
    /**
     * Config source data
     *
     * @var array
     */
    protected $_source = [];
    /**
     * @param MetadataProcessor $processor
     * @param array $data
     */
    public function __construct(Metadata_Processor $processor, array $data)
    {
        /** Clone the array to work around a kink in php7 that modifies the argument by reference */
        $this->_data = $processor->process($this->array_clone($data));
        $this->_source = $data;
    }
    /**
     * Get config source
     *
     * @return array
     */
    public function get_source()
    {
        return $this->_source;
    }
    /**
     * @inheritdoc
     */
    public function get_value($path = null)
    {
        if ($path === null) {
            return $this->_data;
        }
        $keys = explode('/', $path);
        $data = $this->_data;
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
     * @inheritdoc
     */
    public function set_value($path, $value)
    {
        $keys = explode('/', (string) $path);
        $last_key = array_pop($keys);
        $current_element =& $this->_data;
        foreach ($keys as $key) {
            if (!isset($current_element[$key])) {
                $current_element[$key] = [];
            }
            $current_element =& $current_element[$key];
        }
        $current_element[$last_key] = $value;
    }
    /**
     * Copy array by value
     *
     * @param array $data
     * @return array
     */
    private function array_clone(array $data)
    {
        $clone = [];
        foreach ($data as $key => $value) {
            $clone[$key] = $value;
        }
        return $clone;
    }
}