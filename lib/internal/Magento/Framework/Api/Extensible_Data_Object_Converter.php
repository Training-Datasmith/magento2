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
 * Class to convert Extensible Data Object array to flat array
 */
class Extensible_Data_Object_Converter
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
     * Convert AbstractExtensibleObject into a nested array.
     *
     * @param ExtensibleDataInterface $dataObject
     * @param string[] $skipAttributes
     * @param string $dataObjectType
     * @return array
     */
    public function to_nested_array(Extensible_Data_Interface $data_object, $skip_attributes = [], $data_object_type = null)
    {
        if ($data_object_type == null) {
            $data_object_type = get_class($data_object);
        }
        $data_object_array = $this->data_object_processor->build_output_data_array($data_object, $data_object_type);
        //process custom attributes if present
        $data_object_array = $this->process_custom_attributes($data_object_array, $skip_attributes);
        if (!empty($data_object_array[Extensible_Data_Interface::EXTENSION_ATTRIBUTES_KEY])) {
            /** @var array $extensionAttributes */
            $extension_attributes = $data_object_array[Extensible_Data_Interface::EXTENSION_ATTRIBUTES_KEY];
            unset($data_object_array[Extensible_Data_Interface::EXTENSION_ATTRIBUTES_KEY]);
            foreach ($extension_attributes as $attribute_key => $attribute_value) {
                if (!in_array($attribute_key, $skip_attributes)) {
                    $data_object_array[$attribute_key] = $attribute_value;
                }
            }
        }
        return $data_object_array;
    }
    /**
     * Recursive process array to process customer attributes
     *
     * @param array $dataObjectArray
     * @param array $skipAttributes
     * @return array
     */
    private function process_custom_attributes(array $data_object_array, array $skip_attributes): array
    {
        if (!empty($data_object_array[Abstract_Extensible_Object::CUSTOM_ATTRIBUTES_KEY])) {
            /** @var AttributeValue[] $customAttributes */
            $custom_attributes = $data_object_array[Abstract_Extensible_Object::CUSTOM_ATTRIBUTES_KEY];
            unset($data_object_array[Abstract_Extensible_Object::CUSTOM_ATTRIBUTES_KEY]);
            foreach ($custom_attributes as $attribute_value) {
                if (!in_array($attribute_value[Attribute_Value::ATTRIBUTE_CODE], $skip_attributes)) {
                    $data_object_array[$attribute_value[Attribute_Value::ATTRIBUTE_CODE]] = $attribute_value[Attribute_Value::VALUE];
                }
            }
        }
        foreach ($data_object_array as $key => $value) {
            if (is_array($value)) {
                $data_object_array[$key] = $this->process_custom_attributes($value, $skip_attributes);
            }
        }
        return $data_object_array;
    }
    /**
     * Convert AbstractExtensibleObject into flat array.
     *
     * @param ExtensibleDataInterface $dataObject
     * @param string[] $skipCustomAttributes
     * @param string $dataObjectType
     * @return array
     */
    public function to_flat_array(Extensible_Data_Interface $data_object, $skip_custom_attributes = [], $data_object_type = null)
    {
        $data_object_array = $this->to_nested_array($data_object, $skip_custom_attributes, $data_object_type);
        return Convert_Array::to_flat_array($data_object_array);
    }
    /**
     * Convert Extensible Data Object custom attributes in sequential array format.
     *
     * @param array $extensibleObjectData
     * @return array
     */
    public static function convert_custom_attributes_to_sequential_array($extensible_object_data)
    {
        $extensible_object_data[Abstract_Extensible_Object::CUSTOM_ATTRIBUTES_KEY] = array_values($extensible_object_data[Abstract_Extensible_Object::CUSTOM_ATTRIBUTES_KEY]);
        return $extensible_object_data;
    }
}