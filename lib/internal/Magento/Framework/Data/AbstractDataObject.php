<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

/**
 * Base Class for simple data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Abstract_Data_Object
{
    /**
     * @var array
     */
    protected $data;
    /**
     * Return Data Object data in array format.
     *
     * @return array
     */
    public function to_array()
    {
        $data = $this->data;
        $has_to_array = function ($model) {
            return is_object($model) && method_exists($model, 'toArray') && is_callable([$model, 'toArray']);
        };
        foreach ($data as $key => $value) {
            if ($has_to_array($value)) {
                $data[$key] = $value->to_array();
            } elseif (is_array($value)) {
                foreach ($value as $nested_key => $nested_value) {
                    if ($has_to_array($nested_value)) {
                        $value[$nested_key] = $nested_value->to_array();
                    }
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }
    /**
     * Retrieves a value from the data array if set, or null otherwise.
     *
     * @param string $key
     * @return mixed|null
     */
    protected function get($key)
    {
        return $this->data[$key] ?? null;
    }
}