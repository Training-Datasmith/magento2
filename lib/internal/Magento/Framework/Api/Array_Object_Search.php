<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Class to provide possibility to search for any object's property value by the name and value of another property
 */
class Array_Object_Search
{
    /**
     * Search for the value's value by specified key's name-value pair in the object
     * <pre>
     * Example of usage:
     * $data = array(
     *     ValidationRuleBuilderObject('name' => 'min_text_length', 'value' => 0)
     *     ValidationRuleBuilderObject('name' => 'max_text_length', 'value' => 255)
     *     ValidationRuleBuilderObject('anyOtherName' => 'customName', 'anyOtherValue' => 'customValue')
     * );
     *
     * Call:
     * $maxDateValue = ArrayObjectSearch::getArrayElementByName(
     *     $data,
     *     'max_text_length'
     * );
     * By default function looks for `value`'s value by the `name`'s value
     * Result: 255
     *
     * Call:
     * $customValue = ArrayObjectSearch::getArrayElementByName(
     *     $data,
     *     'customName',   //what key value to look for
     *     'anyOtherName', //where to look for
     *     'anyOtherValue' //where to return from
     * );
     * Result: 'customValue'
     * </pre>
     * @param object $data Object to search in
     * @param string $keyValue Value of the key property to search for
     * @param string $keyName Name of the key property to search for
     * @param string $valueName Name of the value property name
     * @return null|mixed
     */
    public static function get_array_element_by_name($data, $key_value, $key_name = 'name', $value_name = 'value')
    {
        $getter = 'get' . ucfirst($key_name);
        if (is_array($data)) {
            foreach ($data as $data_object) {
                if (is_object($data_object) && $data_object->{$getter}() == $key_value) {
                    $value_getter = 'get' . ucfirst($value_name);
                    return $data_object->{$value_getter}();
                }
            }
        }
        return null;
    }
}