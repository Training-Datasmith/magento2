<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Generator;

use InvalidArgumentException;
use Laminas\Code\Generator\Method_Generator;
use Laminas\Code\Generator\Property_Generator;
/**
 * Class code generator
 */
class Class_Generator extends \Laminas\Code\Generator\Class_Generator implements Code_Generator_Interface
{
    /**
     * Possible doc block options
     *
     * @var array
     */
    protected $_doc_block_options = ['shortDescription' => 'setShortDescription', 'longDescription' => 'setLongDescription', 'tags' => 'setTags'];
    /**
     * Possible class property options
     *
     * @var array
     */
    protected $_property_options = ['name' => 'setName', 'const' => 'setConst', 'static' => 'setStatic', 'visibility' => 'setVisibility', 'defaultValue' => 'setDefaultValue'];
    /**
     * Possible class method options
     *
     * @var array
     */
    protected $_method_options = ['name' => 'setName', 'final' => 'setFinal', 'static' => 'setStatic', 'abstract' => 'setAbstract', 'visibility' => 'setVisibility', 'body' => 'setBody', 'returntype' => 'setReturnType'];
    /**
     * Possible method parameter options
     *
     * @var array
     */
    protected $_parameter_options = ['name' => 'setName', 'type' => 'setType', 'defaultValue' => 'setDefaultValue', 'passedByReference' => 'setPassedByReference', 'variadic' => 'setVariadic'];
    /**
     * Set data to object
     *
     * @param object $object
     * @param array $data
     * @param array $map
     * @return void
     */
    protected function _set_data_to_object($object, array $data, array $map)
    {
        foreach ($map as $array_key => $setter_name) {
            if (isset($data[$array_key])) {
                $object->{$setter_name}($data[$array_key]);
            }
        }
    }
    /**
     * Set class dock block
     *
     * @param array $docBlock
     * @return $this
     */
    public function set_class_doc_block(array $doc_block)
    {
        $doc_block_object = new \Laminas\Code\Generator\Doc_Block_Generator();
        $doc_block_object->set_word_wrap(false);
        $this->_set_data_to_object($doc_block_object, $doc_block, $this->_doc_block_options);
        return parent::set_doc_block($doc_block_object);
    }
    /**
     * Add methods
     *
     * @param array $methods
     * @return $this
     */
    public function add_methods(array $methods)
    {
        foreach ($methods as $method_options) {
            $method_object = $this->create_method_generator();
            $this->_set_data_to_object($method_object, $method_options, $this->_method_options);
            if (isset($method_options['parameters']) && is_array($method_options['parameters']) && count($method_options['parameters']) > 0) {
                $parameters_array = [];
                foreach ($method_options['parameters'] as $position => $parameter_options) {
                    $parameter_object = new \Laminas\Code\Generator\Parameter_Generator();
                    $this->_set_data_to_object($parameter_object, $parameter_options, $this->_parameter_options);
                    $parameter_object->set_position((int) $position);
                    $parameters_array[] = $parameter_object;
                }
                $method_object->set_parameters($parameters_array);
            }
            if (isset($method_options['docblock']) && is_array($method_options['docblock'])) {
                $doc_block_object = new \Laminas\Code\Generator\Doc_Block_Generator();
                $doc_block_object->set_word_wrap(false);
                $this->_set_data_to_object($doc_block_object, $method_options['docblock'], $this->_doc_block_options);
                $method_object->set_doc_block($doc_block_object);
            }
            if (!empty($method_options['returnType'])) {
                $method_object->set_return_type($method_options['returnType']);
            }
            $this->add_method_from_generator($method_object);
        }
        return $this;
    }
    /**
     * Add method from MethodGenerator
     *
     * @param  MethodGenerator $method
     * @return $this
     * @throws InvalidArgumentException
     */
    public function add_method_from_generator(Method_Generator $method)
    {
        if (empty($method->get_name()) || !is_string($method->get_name())) {
            throw new InvalidArgumentException('addMethodFromGenerator() expects non-empty string for name');
        }
        return parent::add_method_from_generator($method);
    }
    /**
     * Add properties
     *
     * @param array $properties
     * @return $this
     * @throws InvalidArgumentException
     */
    public function add_properties(array $properties)
    {
        foreach ($properties as $property_options) {
            $property_object = new Property_Generator();
            $this->_set_data_to_object($property_object, $property_options, $this->_property_options);
            if (isset($property_options['docblock'])) {
                $doc_block = $property_options['docblock'];
                if (is_array($doc_block)) {
                    $doc_block_object = new \Laminas\Code\Generator\Doc_Block_Generator();
                    $doc_block_object->set_word_wrap(false);
                    $this->_set_data_to_object($doc_block_object, $doc_block, $this->_doc_block_options);
                    $property_object->set_doc_block($doc_block_object);
                }
            }
            $this->add_property_from_generator($property_object);
        }
        return $this;
    }
    /**
     * Add property from PropertyGenerator
     *
     * @param  PropertyGenerator $property
     * @return $this
     * @throws InvalidArgumentException
     */
    public function add_property_from_generator(Property_Generator $property)
    {
        if (empty($property->get_name()) || !is_string($property->get_name())) {
            throw new InvalidArgumentException('addPropertyFromGenerator() expects non-empty string for name');
        }
        return parent::add_property_from_generator($property);
    }
    /**
     * Instantiate method generator object.
     *
     * @return MethodGenerator
     */
    protected function create_method_generator()
    {
        return new Method_Generator();
    }
    /**
     * Get namespace name
     *
     * @return string|null
     */
    public function get_namespace_name()
    {
        $namespace_name = parent::get_namespace_name();
        if ($namespace_name !== null) {
            $namespace_name = ltrim($namespace_name, '\\') ?: null;
        }
        return $namespace_name;
    }
}