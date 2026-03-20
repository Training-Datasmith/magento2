<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Reflection\Methods_Map;
/**
 * Service class allow populating object from array data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data_Object_Helper
{
    /**
     * @var ObjectFactory
     */
    protected $object_factory;
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $object_processor;
    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $type_processor;
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extension_factory;
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $join_processor;
    /**
     * @var MethodsMap
     */
    protected $methods_map_processor;
    /**
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param MethodsMap $methodsMapProcessor
     */
    public function __construct(Object_Factory $object_factory, \Magento\Framework\Reflection\Data_Object_Processor $object_processor, \Magento\Framework\Reflection\Type_Processor $type_processor, \Magento\Framework\Api\Extension_Attributes_Factory $extension_factory, \Magento\Framework\Api\Extension_Attribute\Join_Processor_Interface $join_processor, Methods_Map $methods_map_processor)
    {
        $this->object_factory = $object_factory;
        $this->object_processor = $object_processor;
        $this->type_processor = $type_processor;
        $this->extension_factory = $extension_factory;
        $this->join_processor = $join_processor;
        $this->methods_map_processor = $methods_map_processor;
    }
    /**
     * Populate data object using data in array format.
     *
     * @param mixed $dataObject
     * @param array $data
     * @param string $interfaceName
     * @return $this
     */
    public function populate_with_array($data_object, array $data, $interface_name)
    {
        if ($data_object instanceof Extensible_Data_Interface) {
            $data = $this->join_processor->extract_extension_attributes(get_class($data_object), $data);
        }
        $this->_set_data_values($data_object, $data, $interface_name);
        return $this;
    }
    /**
     * Update Data Object with the data from array
     *
     * @param mixed $dataObject
     * @param array $data
     * @param string $interfaceName
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _set_data_values($data_object, array $data, $interface_name)
    {
        if (empty($data)) {
            return $this;
        }
        $set_methods = $this->get_setters($data_object);
        $data = $this->set_custom_attributes($data_object, $data, [Custom_Attributes_Data_Interface::CUSTOM_ATTRIBUTES, Custom_Attributes_Data_Interface::CUSTOM_ATTRIBUTES . 'V2']);
        if ($data_object instanceof \Magento\Framework\Model\Abstract_Model) {
            $simple_data = array_filter($data, static function ($e) {
                return is_scalar($e) || is_null($e);
            });
            if (isset($simple_data['id'])) {
                $data_object->set_id($simple_data['id']);
                unset($simple_data['id']);
            }
            $simple_data = array_intersect_key($simple_data, $set_methods);
            $data_object->add_data($simple_data);
            $data = array_diff_key($data, $simple_data);
            if (\count($data) === 0) {
                return $this;
            }
        }
        foreach (array_intersect_key($data, $set_methods) as $key => $value) {
            $method_name = Simple_Data_Object_Converter::snake_case_to_upper_camel_case($key);
            if (!is_array($value)) {
                if ($method_name !== 'ExtensionAttributes' || $value !== null) {
                    if (method_exists($data_object, 'set' . $method_name)) {
                        $data_object->{'set' . $method_name}($value);
                    } else {
                        $data_object->{'setIs' . $method_name}($value);
                    }
                }
            } else {
                $getter_method_name = 'get' . $method_name;
                $this->set_complex_value($data_object, $getter_method_name, 'set' . $method_name, $value, $interface_name);
            }
            unset($data[$key]);
        }
        if ($data_object instanceof Custom_Attributes_Data_Interface) {
            foreach ($data as $key => $value) {
                $data_object->set_custom_attribute($key, $value);
            }
        }
        return $this;
    }
    /**
     * Set complex (like object) value using $methodName based on return type of $getterMethodName
     *
     * @param mixed $dataObject
     * @param string $getterMethodName
     * @param string $methodName
     * @param array $value
     * @param string $interfaceName
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function set_complex_value($data_object, $getter_method_name, $method_name, array $value, $interface_name)
    {
        if ($interface_name == null) {
            $interface_name = get_class($data_object);
        }
        $return_type = $this->methods_map_processor->get_method_return_type($interface_name, $getter_method_name);
        if ($this->type_processor->is_type_simple($return_type)) {
            $data_object->{$method_name}($value);
            return $this;
        }
        if ($this->type_processor->is_array_type($return_type)) {
            $type = $this->type_processor->get_array_item_type($return_type);
            $objects = [];
            foreach ($value as $array_element_data) {
                $object = $this->object_factory->create($type, []);
                $this->populate_with_array($object, $array_element_data, $type);
                $objects[] = $object;
            }
            $data_object->{$method_name}($objects);
            return $this;
        }
        if (is_subclass_of($return_type, \Magento\Framework\Api\Extensible_Data_Interface::class)) {
            $object = $this->object_factory->create($return_type, []);
            $this->populate_with_array($object, $value, $return_type);
        } elseif (is_subclass_of($return_type, \Magento\Framework\Api\Extension_Attributes_Interface::class)) {
            foreach ($value as $extension_attribute_key => $extension_attribute_value) {
                $extension_attribute_getter_method_name = 'get' . Simple_Data_Object_Converter::snake_case_to_upper_camel_case($extension_attribute_key);
                $method_return_type = $this->methods_map_processor->get_method_return_type($return_type, $extension_attribute_getter_method_name);
                $extension_attribute_type = $this->type_processor->is_array_type($method_return_type) ? $this->type_processor->get_array_item_type($method_return_type) : $method_return_type;
                if ($this->type_processor->is_type_simple($extension_attribute_type)) {
                    $value[$extension_attribute_key] = $extension_attribute_value;
                } else if ($this->type_processor->is_array_type($method_return_type)) {
                    foreach ($extension_attribute_value as $key => $extension_attribute_array_value) {
                        $extension_attribute = $this->object_factory->create($extension_attribute_type, []);
                        $this->populate_with_array($extension_attribute, $extension_attribute_array_value, $extension_attribute_type);
                        $value[$extension_attribute_key][$key] = $extension_attribute;
                    }
                } else {
                    $value[$extension_attribute_key] = $this->object_factory->create($extension_attribute_type, ['data' => $extension_attribute_value]);
                }
            }
            $object = $this->extension_factory->create(get_class($data_object), ['data' => $value]);
        } else {
            $object = $this->object_factory->create($return_type, $value);
        }
        $data_object->{$method_name}($object);
        return $this;
    }
    /**
     * Merges second object onto the first
     *
     * @param string $interfaceName
     * @param mixed $firstDataObject
     * @param mixed $secondDataObject
     * @return $this
     * @throws \LogicException
     */
    public function merge_data_objects($interface_name, $first_data_object, $second_data_object)
    {
        if (!$first_data_object instanceof $interface_name || !$second_data_object instanceof $interface_name) {
            throw new \LogicException('Wrong prototype object given. It can only be of "' . $interface_name . '" type.');
        }
        $second_object_array = $this->object_processor->build_output_data_array($second_data_object, $interface_name);
        $this->_set_data_values($first_data_object, $second_object_array, $interface_name);
        return $this;
    }
    /**
     * Filter attribute value objects for a provided data interface type from an array of custom attribute value objects
     *
     * @param AttributeValue[] $attributeValues Array of custom attribute
     * @param string $type Data interface type
     * @return AttributeValue[]
     */
    public function get_custom_attribute_value_by_type(array $attribute_values, $type)
    {
        $attribute_value_array = [];
        if (empty($attribute_values)) {
            return $attribute_value_array;
        }
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value->get_value() instanceof $type) {
                $attribute_value_array[] = $attribute_value;
            }
        }
        return $attribute_value_array;
    }
    /** @var array  */
    private array $setters_cache = [];
    /**
     * Get list of setters for object
     *
     * @param object $dataObject
     * @return array
     */
    private function get_setters(object $data_object): array
    {
        $class = get_class($data_object);
        if (!isset($this->setters_cache[$class])) {
            $data_object_methods = get_class_methods($class);
            // use regexp to manipulate with method list as it use jit starting with PHP 7.3
            $setters = array_filter(explode(',', strtolower(
                // (0) remove all not setter
                // (1) add _ before upper letter
                // (2) remove set_ in start of name
                // (3) add name without is_ prefix
                preg_replace(['/(^|,)(?!set)[^,]*/S', '/([A-Z])/S', '/(^|,)set_/iS', '/(^|,)is_([^,]+)/is'], ['', '_$1', '$1', '$1$2,is_$2'], implode(',', $data_object_methods))
            )));
            $this->setters_cache[$class] = array_flip($setters);
        }
        return $this->setters_cache[$class];
    }
    /**
     * Set custom attributes using the $attributeKeys parameter.
     *
     * @param mixed $dataObject
     * @param array $data
     * @param array $attributeKeys
     * @return array
     */
    public function set_custom_attributes(mixed $data_object, array $data, array $attribute_keys): array
    {
        foreach ($attribute_keys as $attribute_key) {
            if ($data_object instanceof Extensible_Data_Interface && !empty($data[$attribute_key])) {
                foreach ($data[$attribute_key] as $custom_attribute) {
                    $data_object->set_custom_attribute($custom_attribute[Attribute_Interface::ATTRIBUTE_CODE], $custom_attribute[Attribute_Interface::VALUE]);
                }
                unset($data[$attribute_key]);
            }
        }
        return $data;
    }
}