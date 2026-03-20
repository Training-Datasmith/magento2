<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api;

/**
 * Base Class for simple data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 *
 * @api
 */
abstract class Abstract_Simple_Object
{
    /**
     * @var array
     */
    protected $_data;
    /**
     * Initialize internal storage
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->_data = $data;
    }
    /**
     * Retrieves a value from the data array if set, or null otherwise.
     *
     * @param string $key
     * @return mixed|null
     */
    protected function _get($key)
    {
        return $this->_data[$key] ?? null;
    }
    /**
     * Set value for the given key
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set_data($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }
    /**
     * Return Data Object data in array format.
     *
     * @return array
     */
    public function __to_array()
    {
        $data = $this->_data;
        $has_to_array = function ($model) {
            return is_object($model) && method_exists($model, '__toArray') && is_callable([$model, '__toArray']);
        };
        foreach ($data as $key => $value) {
            if ($has_to_array($value)) {
                $data[$key] = $value->__to_array();
            } elseif (is_array($value)) {
                foreach ($value as $nested_key => $nested_value) {
                    if ($has_to_array($nested_value)) {
                        $value[$nested_key] = $nested_value->__to_array();
                    }
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }
}