<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Convert\Convert_Array;
use Magento\Framework\Reflection\Data_Object_Processor;
/**
 * Data object converter.
 */
class Simple_Data_Object_Converter
{
    /**
     * @var DataObjectProcessor
     */
    protected $data_object_processor;
    /**
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(Data_Object_Processor $data_object_processor)
    {
        $this->data_object_processor = $data_object_processor;
    }
    /**
     * Convert nested array into flat array.
     *
     * @param ExtensibleDataInterface $dataObject
     * @param string $dataObjectType
     * @return array
     */
    public function to_flat_array(Extensible_Data_Interface $data_object, $data_object_type = null)
    {
        if ($data_object_type === null) {
            $data_object_type = get_class($data_object);
        }
        $data = $this->data_object_processor->build_output_data_array($data_object, $data_object_type);
        return Convert_Array::to_flat_array($data);
    }
    /**
     * Convert keys to camelCase
     *
     * @param array $dataArray
     * @return \stdClass
     */
    public function convert_keys_to_camel_case(array $data_array)
    {
        $response = [];
        if (isset($data_array[Abstract_Extensible_Object::CUSTOM_ATTRIBUTES_KEY])) {
            $data_array = Extensible_Data_Object_Converter::convert_custom_attributes_to_sequential_array($data_array);
        }
        foreach ($data_array as $field_name => $field_value) {
            if (is_array($field_value) && !$this->_is_simple_sequential_array($field_value)) {
                $field_value = $this->convert_keys_to_camel_case($field_value);
            }
            $field_name = lcfirst(str_replace('_', '', ucwords($field_name, '_')));
            $response[$field_name] = $field_value;
        }
        return $response;
    }
    /**
     * Check if the array is a simple(one dimensional and not nested) and a sequential(non-associative) array
     *
     * @param array $data
     * @return bool
     */
    protected function _is_simple_sequential_array(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_string($key) || is_array($value)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Convert multidimensional object/array into multidimensional array of primitives.
     *
     * @param object|array $input
     * @param bool $removeItemNode Remove Item node from arrays if true
     * @return array
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert_std_object_to_array($input, $remove_item_node = false)
    {
        if (!is_object($input) && !is_array($input)) {
            throw new \InvalidArgumentException('Input argument must be an array or object');
        }
        // @codingStandardsIgnoreStart
        if ($remove_item_node && (isset($input->item) || isset($input->Map))) {
            $node = isset($input->item) ? $input->item : $input->Map;
            /**
             * In case when only one Data object value is passed, it will not be wrapped into a subarray
             * within any additional node. If several Data object values are passed, they will be wrapped into
             * an indexed array within item or Map node.
             */
            $input = is_object($node) ? [$node] : $node;
        }
        // @codingStandardsIgnoreEnd
        $result = [];
        foreach ((array) $input as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $result[$key] = $this->convert_std_object_to_array($value, $remove_item_node);
            } else {
                $result[$key] = $value;
            }
        }
        return $this->_unpack_associative_array($result);
    }
    /**
     * Unpack associative array packed by SOAP server into key-value
     *
     * @param mixed $data
     * @return array Unpacked associative array if array was passed as argument or original value otherwise
     */
    protected function _unpack_associative_array($data)
    {
        if (!is_array($data)) {
            return $data;
        } else {
            foreach ($data as $key => $value) {
                if (is_array($value) && count($value) == 2 && isset($value['key']) && isset($value['value'])) {
                    $data[$value['key']] = $this->_unpack_associative_array($value['value']);
                    unset($data[$key]);
                } else {
                    $data[$key] = $this->_unpack_associative_array($value);
                }
            }
            return $data;
        }
    }
    /**
     * Converts an input string from snake_case to upper CamelCase.
     *
     * @param string $input
     * @return string
     */
    public static function snake_case_to_upper_camel_case($input)
    {
        return $input !== null ? str_replace('_', '', ucwords($input, '_')) : '';
    }
    /**
     * Converts an input string from snake_case to camelCase.
     *
     * @param string $input
     * @return string
     */
    public static function snake_case_to_camel_case($input)
    {
        return lcfirst(self::snake_case_to_upper_camel_case($input));
    }
    /**
     * Convert a CamelCase string read from method into field key in snake_case
     *
     * For example [DefaultShipping => default_shipping, Postcode => postcode]
     *
     * @param string $name
     * @return string
     */
    public static function camel_case_to_snake_case($name)
    {
        return $name !== null ? strtolower(ltrim(preg_replace('/([A-Z])/m', '_$1', $name), '_')) : '';
    }
}