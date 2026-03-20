<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Code\Generator;

/**
 * Class Repository
 */
class Mapper extends \Magento\Framework\Code\Generator\Entity_Abstract
{
    /**
     * Entity type
     */
    public const ENTITY_TYPE = 'mapper';
    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _get_class_properties()
    {
        $properties = [['name' => $this->_get_source_builder_property_name(), 'visibility' => 'protected', 'docblock' => ['shortDescription' => $this->_get_source_builder_property_name(), 'tags' => [['name' => 'var', 'description' => $this->get_source_class_name() . 'Builder']]]], ['name' => 'registry', 'visibility' => 'protected', 'defaultValue' => [], 'docblock' => ['shortDescription' => $this->get_source_class_name() . '[]', 'tags' => [['name' => 'var', 'description' => 'array']]]]];
        return $properties;
    }
    /**
     * Returns source factory property Name
     *
     * @return string
     */
    protected function _get_source_builder_property_name()
    {
        return lcfirst($this->get_source_class_name_without_namespace()) . 'Builder';
    }
    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _get_default_constructor_definition()
    {
        return ['name' => '__construct', 'parameters' => [['name' => $this->_get_source_builder_property_name(), 'type' => $this->get_source_class_name() . 'Builder']], 'body' => '$this->' . $this->_get_source_builder_property_name() . ' = $' . $this->_get_source_builder_property_name() . ';', 'docblock' => ['shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor', 'tags' => [['name' => 'param', 'description' => $this->get_source_class_name() . ' $' . $this->_get_source_builder_property_name()]]]];
    }
    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _get_class_methods()
    {
        $construct = $this->_get_default_constructor_definition();
        $body = '$this->' . $this->_get_source_builder_property_name() . '->populateWithArray($object->getData());' . "\nreturn \$this->" . $this->_get_source_builder_property_name() . '->create();';
        $extract = ['name' => 'extractDto', 'parameters' => [['name' => 'object', 'type' => '\\' . \Magento\Framework\Model\Abstract_Model::class]], 'body' => $body, 'docblock' => ['shortDescription' => 'Extract data object from model', 'tags' => [['name' => 'param', 'description' => '\Magento\Framework\Model\AbstractModel $object'], ['name' => 'return', 'description' => $this->get_source_class_name()]]]];
        return [$construct, $extract];
    }
    /**
     * {@inheritdoc}
     */
    protected function _validate_data()
    {
        $result = parent::_validate_data();
        if ($result) {
            $source_class_name = $this->get_source_class_name();
            $result_class_name = $this->_get_result_class_name();
            if ($result_class_name !== $source_class_name . 'Mapper') {
                $this->_add_error('Invalid Mapper class name [' . $result_class_name . ']. Use ' . $source_class_name . 'Mapper');
                $result = false;
            }
        }
        return $result;
    }
}