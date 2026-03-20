<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data_Object;

use Magento\Framework\Api\Abstract_Simple_Object;
use Magento\Framework\Api\Extensible_Data_Interface;
use Magento\Framework\Api\Extension_Attributes_Factory;
use Magento\Framework\Data_Object;
use Magento\Framework\Data_Object\Copy\Config;
use Magento\Framework\Event\Manager_Interface;
/**
 * Utility class for copying data sets between objects
 *
 * @api
 */
class Copy
{
    /**
     * @var Config
     */
    protected $fieldset_config;
    /**
     * @var ManagerInterface
     */
    protected $event_manager = null;
    /**
     * @var ExtensionAttributesFactory
     */
    protected $extension_attributes_factory;
    /**
     * @param ManagerInterface $eventManager
     * @param Config $fieldsetConfig
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(Manager_Interface $event_manager, Config $fieldset_config, Extension_Attributes_Factory $extension_attributes_factory)
    {
        $this->event_manager = $event_manager;
        $this->fieldset_config = $fieldset_config;
        $this->extension_attributes_factory = $extension_attributes_factory;
    }
    /**
     * Copy data from object|array to object|array containing fields from fieldset matching an aspect.
     *
     * Contents of $aspect are a field name in target object or array.
     * If targetField attribute is not provided - will be used the same name as in the source object or array.
     *
     * @param string $fieldset
     * @param string $aspect
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $target
     * @param string $root
     *
     * @return array|DataObject|null the value of $target
     * @throws \InvalidArgumentException
     */
    public function copy_fieldset_to_target($fieldset, $aspect, $source, $target, $root = 'global')
    {
        if (!$this->_is_fieldset_input_valid($source, $target)) {
            return null;
        }
        $fields = $this->fieldset_config->get_fieldset($fieldset, $root);
        if ($fields === null) {
            return $target;
        }
        $target_is_array = is_array($target);
        foreach ($fields as $code => $node) {
            if (empty($node[$aspect])) {
                continue;
            }
            $value = $this->_get_fieldset_field_value($source, $code);
            $target_code = (string) $node[$aspect];
            $target_code = $target_code == '*' ? $code : $target_code;
            $target = $this->_set_fieldset_field_value($target, $target_code, $value);
        }
        $target = $this->dispatch_copy_field_set_event($fieldset, $aspect, $source, $target, $root, $target_is_array);
        return $target;
    }
    /**
     * Dispatch copy fieldset event
     *
     * @param string $fieldset
     * @param string $aspect
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $target
     * @param string $root
     * @param bool $targetIsArray
     *
     * @return DataObject|mixed
     */
    protected function dispatch_copy_field_set_event($fieldset, $aspect, $source, $target, $root, $target_is_array)
    {
        $event_name = sprintf('core_copy_fieldset_%s_%s', $fieldset, $aspect);
        if ($target_is_array) {
            $target = new Data_Object($target);
        }
        $this->event_manager->dispatch($event_name, ['target' => $target, 'source' => $source, 'root' => $root]);
        if ($target_is_array) {
            $target = $target->get_data();
        }
        return $target;
    }
    /**
     * Get data from object|array to object|array containing fields from fieldset matching an aspect.
     *
     * @param string $fieldset
     * @param string $aspect a field name
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param string $root
     *
     * @return array
     */
    public function get_data_from_fieldset($fieldset, $aspect, $source, $root = 'global')
    {
        if (!$this->is_input_argument_valid($source)) {
            return null;
        }
        $fields = $this->fieldset_config->get_fieldset($fieldset, $root);
        if ($fields === null) {
            return null;
        }
        $data = [];
        foreach ($fields as $code => $node) {
            if (empty($node[$aspect])) {
                continue;
            }
            $value = $this->_get_fieldset_field_value($source, $code);
            $target_code = (string) $node[$aspect];
            $target_code = $target_code == '*' ? $code : $target_code;
            $data[$target_code] = $value;
        }
        return $data;
    }
    /**
     * Check if source and target are valid input for converting using fieldset
     *
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $target
     *
     * @return bool
     */
    protected function _is_fieldset_input_valid($source, $target)
    {
        return $this->is_input_argument_valid($source) && $this->is_input_argument_valid($target);
    }
    /**
     * Verify that we can access data from input object.
     *
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $object
     *
     * @return bool
     */
    private function is_input_argument_valid($object): bool
    {
        return is_array($object) || $object instanceof Data_Object || $object instanceof Extensible_Data_Interface || $object instanceof Abstract_Simple_Object;
    }
    /**
     * Get value of source by code
     *
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $source
     * @param string $code
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function _get_fieldset_field_value($source, $code)
    {
        switch (true) {
            case is_array($source):
                $value = isset($source[$code]) ? $source[$code] : null;
                break;
            case $source instanceof Extensible_Data_Interface:
                $value = $this->get_attribute_value_from_extensible_object($source, $code);
                break;
            case $source instanceof Data_Object:
                $value = $source->get_data_using_method($code);
                break;
            case $source instanceof Abstract_Simple_Object:
                $source_array = $source->__to_array();
                $value = isset($source_array[$code]) ? $source_array[$code] : null;
                break;
            default:
                throw new \InvalidArgumentException('Source should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject');
        }
        return $value;
    }
    /**
     * Set value of target by code
     *
     * @param array|DataObject|ExtensibleDataInterface|AbstractSimpleObject $target
     * @param string $targetCode
     * @param mixed $value
     *
     * @return array|DataObject|ExtensibleDataInterface|AbstractSimpleObject
     * @throws \InvalidArgumentException
     */
    protected function _set_fieldset_field_value($target, $target_code, $value)
    {
        switch (true) {
            case is_array($target):
                $target[$target_code] = $value;
                break;
            case $target instanceof Extensible_Data_Interface:
                $this->set_attribute_value_from_extensible_object($target, $target_code, $value);
                break;
            case $target instanceof Data_Object:
                $target->set_data_using_method($target_code, $value);
                break;
            case $target instanceof Abstract_Simple_Object:
                $target->set_data($target_code, $value);
                break;
            default:
                throw new \InvalidArgumentException('Source should be array, Magento Object, ExtensibleDataInterface, or AbstractSimpleObject');
        }
        return $target;
    }
    /**
     * Access the extension get method
     *
     * @param ExtensibleDataInterface $source
     * @param string $code
     *
     * @return mixed
     * @throws \InvalidArgumentException
     *
     * @deprecated 102.0.3
     * @see \Magento\Framework\DataObject\Copy::getAttributeValueFromExtensibleObject
     */
    protected function get_attribute_value_from_extensible_data_object($source, $code)
    {
        return $this->get_attribute_value_from_extensible_object($source, $code);
    }
    /**
     * Get Attribute Value from Extensible Object Data with fallback to DataObject or AbstractSimpleObject.
     *
     * @param ExtensibleDataInterface $source
     * @param string $code
     *
     * @return mixed|null
     */
    private function get_attribute_value_from_extensible_object(Extensible_Data_Interface $source, string $code)
    {
        $method = 'get' . str_replace('_', '', ucwords($code, '_'));
        $method_exists = method_exists($source, $method);
        if ($method_exists === true) {
            return $source->{$method}();
        }
        $extension_attributes = $source->get_extension_attributes();
        if ($extension_attributes) {
            $method_exists = method_exists($extension_attributes, $method);
            if ($method_exists) {
                return $extension_attributes->{$method}();
            }
        }
        if ($source instanceof Data_Object) {
            return $source->get_data_using_method($code);
        }
        if ($source instanceof Abstract_Simple_Object) {
            $source_array = $source->__to_array();
            return isset($source_array[$code]) ? $source_array[$code] : null;
        }
        throw new \InvalidArgumentException('Attribute in object does not exist.');
    }
    /**
     * Access the extension set method
     *
     * @param ExtensibleDataInterface $target
     * @param string $code
     * @param mixed $value
     *
     * @return void
     * @throws \InvalidArgumentException
     *
     * @deprecated 102.0.3
     * @see \Magento\Framework\DataObject\Copy::setAttributeValueFromExtensibleObject
     */
    protected function set_attribute_value_from_extensible_data_object(Extensible_Data_Interface $target, $code, $value)
    {
        $this->set_attribute_value_from_extensible_object($target, $code, $value);
    }
    /**
     * Set Attribute Value for Extensible Object Data with fallback to DataObject or AbstractSimpleObject.
     *
     * @param ExtensibleDataInterface $target
     * @param string $code
     * @param mixed $value
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    private function set_attribute_value_from_extensible_object(Extensible_Data_Interface $target, string $code, $value): void
    {
        $method = 'set' . str_replace('_', '', ucwords($code, '_'));
        $method_exists = method_exists($target, $method);
        if ($method_exists) {
            $target->{$method}($value);
            return;
        }
        $extension_attributes = $target->get_extension_attributes();
        if ($extension_attributes === null) {
            $extension_attributes = $this->extension_attributes_factory->create(get_class($target));
        }
        if (method_exists($extension_attributes, $method)) {
            $extension_attributes->{$method}($value);
            $target->set_extension_attributes($extension_attributes);
            return;
        }
        if ($target instanceof Data_Object) {
            $target->set_data_using_method($code, $value);
            return;
        }
        if ($target instanceof Abstract_Simple_Object) {
            $target->set_data($code, $value);
            return;
        }
        throw new \InvalidArgumentException('Attribute in object does not exist.');
    }
}