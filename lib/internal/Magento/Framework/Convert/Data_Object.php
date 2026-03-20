<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Convert;

/**
 * Default converter for \Magento\Framework\DataObjects to arrays
 *
 * @api
 */
class Data_Object
{
    /** Constant used to mark cycles in the input array/objects */
    public const CYCLE_DETECTED_MARK = '*** CYCLE DETECTED ***';
    /**
     * Convert input data into an array and return the resulting array.
     * The resulting array should not contain any objects.
     *
     * @param array $data input data
     * @return array Data converted to an array
     */
    public function convert_data_to_array($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $result[$key] = $this->_convert_object_to_array($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    /**
     * Converts a \Magento\Framework\DataObject into an array, including any children objects
     *
     * @param mixed $obj array or object to convert
     * @param array $objects array of object hashes used for cycle detection
     * @return array|string Converted object or CYCLE_DETECTED_MARK
     */
    protected function _convert_object_to_array($obj, &$objects = [])
    {
        $data = [];
        if (is_object($obj)) {
            $hash = spl_object_hash($obj);
            if (!empty($objects[$hash])) {
                return self::CYCLE_DETECTED_MARK;
            }
            $objects[$hash] = true;
            if ($obj instanceof \Magento\Framework\Data_Object) {
                $data = $obj->get_data();
            } else {
                $data = (array) $obj;
            }
        } elseif (is_array($obj)) {
            $data = $obj;
        }
        $result = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $result[$key] = $value;
            } elseif (is_array($value)) {
                $result[$key] = $this->_convert_object_to_array($value, $objects);
            } elseif ($value instanceof \Magento\Framework\Data_Object) {
                $result[$key] = $this->_convert_object_to_array($value, $objects);
            }
        }
        return $result;
    }
    /**
     * Converts the list of objects into an array of the form: [ [ 'label' => <id>, 'value' => <value> ], ... ].
     *
     *
     * The <id> and <value> values are taken from the objects in the list using the $idField and $valueField
     * parameters, which can be either the name of the field to use, or a closure.
     *
     * @param array $items
     * @param string|callable $idField
     * @param string|callable $valueField
     * @return array
     */
    public function to_option_array(array $items, $id_field, $value_field)
    {
        $options = [];
        foreach ($items as $item) {
            $options[] = ['value' => $this->_invoke_getter($item, $id_field), 'label' => $this->_invoke_getter($item, $value_field)];
        }
        return $options;
    }
    /**
     * Converts the list of objects into an array of the form: [ <id> => <value>, ... ].
     *
     *
     * The <id> and <value> values are taken from the objects in the list using the $idField and $valueField parameters,
     * which can be either the name of the field to use, or a closure.
     *
     * @param array $items
     * @param string|callable $idField
     * @param string|callable $valueField
     * @return array
     */
    public function to_option_hash(array $items, $id_field, $value_field)
    {
        $options = [];
        foreach ($items as $item) {
            $options[$this->_invoke_getter($item, $id_field)] = $this->_invoke_getter($item, $value_field);
        }
        return $options;
    }
    /**
     * Returns the value of the property represented by $field on the $item object.
     *
     *
     * When $field is a closure, the $item parameter is passed to the $field method, otherwise the $field is assumed
     * to be a property name, and the associated get method is invoked on the $item instead.
     *
     * @param mixed $item
     * @param string|callable $field
     * @return mixed
     */
    protected function _invoke_getter($item, $field)
    {
        if (is_callable($field)) {
            // if $field is a closure, use that on the item
            return $field($item);
        } else {
            // otherwise, turn it into a call to the item's getter method
            $method_name = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            return $item->{$method_name}();
        }
    }
}