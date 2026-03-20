<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Api\Extension_Attribute\Config\Converter;
use Magento\Framework\Api\Simple_Data_Object_Converter;
use Magento\Framework\Code\Generator\Defined_Classes;
use Magento\Framework\Code\Generator\Io;
/**
 * Code generator for data object extensions.
 */
class Extension_Attributes_Generator extends \Magento\Framework\Code\Generator\Entity_Abstract
{
    public const ENTITY_TYPE = 'extension';
    public const EXTENSION_SUFFIX = 'Extension';
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config
     */
    protected $config;
    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    private $type_processor;
    /**
     * @var array
     */
    protected $all_custom_attributes;
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Api\ExtensionAttribute\Config $config
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io $ioObject
     * @param \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     */
    public function __construct(\Magento\Framework\Api\Extension_Attribute\Config $config, $source_class_name = null, $result_class_name = null, ?Io $io_object = null, ?\Magento\Framework\Code\Generator\Code_Generator_Interface $class_generator = null, ?Defined_Classes $defined_classes = null)
    {
        $source_class_name .= 'Interface';
        $this->config = $config;
        parent::__construct($source_class_name, $result_class_name, $io_object, $class_generator, $defined_classes);
    }
    /**
     * Get type processor
     *
     * @return \Magento\Framework\Reflection\TypeProcessor
     * @deprecated 100.1.0
     */
    private function get_type_processor()
    {
        if ($this->type_processor === null) {
            $this->type_processor = \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Framework\Reflection\Type_Processor::class);
        }
        return $this->type_processor;
    }
    /**
     * @inheritdoc
     */
    protected function _get_default_constructor_definition()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    protected function _get_class_properties()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    protected function _get_class_methods()
    {
        $methods = [];
        foreach ($this->get_custom_attributes() as $attribute_name => $attribute_metadata) {
            $attribute_type = $attribute_metadata[Converter::DATA_TYPE];
            $property_name = Simple_Data_Object_Converter::snake_case_to_camel_case($attribute_name);
            $getter_name = 'get' . ucfirst($property_name);
            $setter_name = 'set' . ucfirst($property_name);
            $methods[] = ['name' => $getter_name, 'body' => "return \$this->_get('{$attribute_name}');", 'docblock' => ['tags' => [['name' => 'return', 'description' => $attribute_type . '|null']]]];
            $parameters = ['name' => $property_name];
            // If the attribute type is a valid type declaration (e.g., interface, class, array) then use it to enforce
            // constraints on the generated setter methods
            if ($this->get_type_processor()->is_valid_type_declaration($attribute_type)) {
                $parameters['type'] = $attribute_type;
            }
            $methods[] = ['name' => $setter_name, 'parameters' => [$parameters], 'body' => "\$this->setData('{$attribute_name}', \${$property_name});" . PHP_EOL . 'return $this;', 'docblock' => ['tags' => [['name' => 'param', 'description' => "{$attribute_type} \${$property_name}"], ['name' => 'return', 'description' => '$this']]]];
        }
        return $methods;
    }
    /**
     * @inheritdoc
     */
    protected function _validate_data()
    {
        $class_name_validation_results = $this->validate_result_class_name();
        return parent::_validate_data() && $class_name_validation_results;
    }
    /**
     * @inheritdoc
     */
    protected function _generate_code()
    {
        $this->_class_generator->set_implemented_interfaces([$this->_get_result_class_name() . 'Interface']);
        $this->_class_generator->set_extended_class($this->get_extended_class());
        return parent::_generate_code();
    }
    /**
     * Get class, which should be used as a parent for generated class.
     *
     * @return string
     */
    protected function get_extended_class()
    {
        return '\\' . \Magento\Framework\Api\Abstract_Simple_Object::class;
    }
    /**
     * Retrieve a list of attributes associated with current source class.
     *
     * @return array
     */
    protected function get_custom_attributes()
    {
        if (!isset($this->all_custom_attributes)) {
            $this->all_custom_attributes = $this->config->get();
        }
        $data_interface = $this->get_source_class_name() !== null ? ltrim($this->get_source_class_name(), '\\') : '';
        if (isset($this->all_custom_attributes[$data_interface])) {
            foreach ($this->all_custom_attributes[$data_interface] as $attribute_name => $attribute_metadata) {
                $attribute_type = $attribute_metadata[Converter::DATA_TYPE];
                if ($attribute_type !== null && strpos($attribute_type, '\\') !== false) {
                    /** Add preceding slash to class names, while leaving primitive types as is */
                    $attribute_type = $this->_get_fully_qualified_class_name($attribute_type);
                    $this->all_custom_attributes[$data_interface][$attribute_name][Converter::DATA_TYPE] = $this->_get_fully_qualified_class_name($attribute_type);
                }
            }
            return $this->all_custom_attributes[$data_interface];
        } else {
            return [];
        }
    }
    /**
     * Ensure that result class name corresponds to the source class name.
     *
     * @return bool
     */
    protected function validate_result_class_name()
    {
        $result = true;
        $source_class_name = $this->get_source_class_name() ?? '';
        $result_class_name = $this->_get_result_class_name();
        $interface_suffix = 'Interface';
        $expected_result_class_name = substr($source_class_name, 0, -strlen($interface_suffix)) . self::EXTENSION_SUFFIX;
        if ($result_class_name !== $expected_result_class_name) {
            $this->_add_error('Invalid extension name [' . $result_class_name . ']. Use ' . $expected_result_class_name);
            $result = false;
        }
        return $result;
    }
}