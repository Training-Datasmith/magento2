<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Generator;

use Laminas\Code\Generator\Value_Generator;
use Magento\Framework\Get_Parameter_Class_Trait;
/**
 * Abstract entity
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Entity_Abstract
{
    use Get_Parameter_Class_Trait;
    /**
     * Entity type abstract
     */
    public const ENTITY_TYPE = 'abstract';
    /**
     * @var string[]
     */
    private $_errors = [];
    /**
     * Source model class name
     *
     * @var string
     */
    private $_source_class_name;
    /**
     * Result model class name
     *
     * @var string
     */
    private $_result_class_name;
    /**
     * @var Io
     */
    private $_io_object;
    /**
     * Class generator object
     *
     * @var \Magento\Framework\Code\Generator\CodeGeneratorInterface
     */
    protected $_class_generator;
    /**
     * @var DefinedClasses
     */
    private $defined_classes;
    /**
     * @param null|string $sourceClassName
     * @param null|string $resultClassName
     * @param Io $ioObject
     * @param \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     */
    public function __construct($source_class_name = null, $result_class_name = null, ?Io $io_object = null, ?\Magento\Framework\Code\Generator\Code_Generator_Interface $class_generator = null, ?Defined_Classes $defined_classes = null)
    {
        if ($io_object) {
            $this->_io_object = $io_object;
        } else {
            $this->_io_object = new Io(new \Magento\Framework\Filesystem\Driver\File());
        }
        if ($class_generator) {
            $this->_class_generator = $class_generator;
        } else {
            $this->_class_generator = new Class_Generator();
        }
        if ($defined_classes) {
            $this->defined_classes = $defined_classes;
        } else {
            $this->defined_classes = new Defined_Classes();
        }
        $this->_source_class_name = $this->_get_fully_qualified_class_name($source_class_name);
        if ($result_class_name) {
            $this->_result_class_name = $this->_get_fully_qualified_class_name($result_class_name);
        } elseif ($this->_source_class_name) {
            $this->_result_class_name = $this->_get_default_result_class_name($this->_source_class_name);
        }
    }
    /**
     * Generation template method
     *
     * @return bool
     */
    public function generate()
    {
        try {
            if ($this->_validate_data()) {
                $source_code = $this->_generate_code();
                if ($source_code) {
                    $file_name = $this->_io_object->generate_result_file_name($this->_get_result_class_name());
                    $this->_io_object->write_result_file($file_name, $source_code);
                    return $file_name;
                } else {
                    $this->_add_error('Can\'t generate source code.');
                }
            }
        } catch (\Exception $e) {
            $this->_add_error($e->get_message());
        }
        return false;
    }
    /**
     * List of occurred generation errors
     *
     * @return string[]
     */
    public function get_errors()
    {
        return $this->_errors;
    }
    /**
     * Get full source class name, with namespace
     *
     * @return string
     */
    public function get_source_class_name()
    {
        return $this->_source_class_name;
    }
    /**
     * Get source class without namespace.
     *
     * @return string
     */
    public function get_source_class_name_without_namespace()
    {
        $parts = explode('\\', ltrim($this->get_source_class_name(), '\\'));
        return end($parts);
    }
    /**
     * Get fully qualified class name
     *
     * @param string $className
     * @return string
     */
    protected function _get_fully_qualified_class_name($class_name)
    {
        return $class_name ? '\\' . ltrim($class_name, '\\') : '';
    }
    /**
     * Get result class name
     *
     * @return string
     */
    protected function _get_result_class_name()
    {
        return $this->_result_class_name;
    }
    /**
     * Get default result class name
     *
     * @param string $modelClassName
     * @return string
     */
    protected function _get_default_result_class_name($model_class_name)
    {
        return $model_class_name . ucfirst(static::ENTITY_TYPE);
    }
    /**
     * Returns list of properties for class generator
     *
     * @return array
     */
    protected function _get_class_properties()
    {
        $object_manager = ['name' => '_objectManager', 'visibility' => 'protected', 'docblock' => ['shortDescription' => 'Object Manager instance', 'tags' => [['name' => 'var', 'description' => '\\' . \Magento\Framework\Object_Manager_Interface::class]]]];
        return [$object_manager];
    }
    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    abstract protected function _get_default_constructor_definition();
    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    abstract protected function _get_class_methods();
    /**
     * Generate code
     *
     * @return string
     */
    protected function _generate_code()
    {
        $this->_class_generator->set_name($this->_get_result_class_name())->add_properties($this->_get_class_properties())->add_methods($this->_get_class_methods())->set_class_doc_block($this->_get_class_doc_block());
        return $this->_get_generated_code();
    }
    /**
     * Add error message
     *
     * @param string $message
     * @return $this
     */
    protected function _add_error($message)
    {
        $this->_errors[] = $message;
        return $this;
    }
    /**
     * Validate data
     *
     * @return bool
     */
    protected function _validate_data()
    {
        $source_class_name = $this->get_source_class_name();
        $result_class_name = $this->_get_result_class_name();
        $result_dir = $this->_io_object->get_result_file_directory($result_class_name);
        if (!$this->defined_classes->is_class_loadable($source_class_name)) {
            $this->_add_error('Source class ' . $source_class_name . ' doesn\'t exist.');
            return false;
        } elseif (!$this->_io_object->make_result_file_directory($result_class_name) && !$this->_io_object->file_exists($result_dir)) {
            $this->_add_error('Can\'t create directory ' . $result_dir . '.');
            return false;
        }
        return true;
    }
    /**
     * Get class DocBlock
     *
     * @return array
     */
    protected function _get_class_doc_block()
    {
        $description = ucfirst(static::ENTITY_TYPE) . ' class for @see ' . $this->get_source_class_name();
        return ['shortDescription' => $description];
    }
    /**
     * Get generated code
     *
     * @return string
     */
    protected function _get_generated_code()
    {
        $source_code = $this->_class_generator->generate();
        return $this->_fix_code_style($source_code);
    }
    /**
     * Fix code style
     *
     * @param string $sourceCode
     * @return string
     */
    protected function _fix_code_style($source_code)
    {
        $source_code = str_replace(' array (', ' array(', $source_code);
        $source_code = preg_replace("/{\n{2,}/m", "{\n", $source_code);
        $source_code = preg_replace("/\n{2,}}/m", "\n}", $source_code);
        return $source_code;
    }
    /**
     * Get value generator for null default value
     *
     * @return ValueGenerator
     */
    protected function _get_null_default_value()
    {
        $value = new Value_Generator(null, Value_Generator::TYPE_NULL);
        return $value;
    }
    /**
     * Extract parameter type
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param \ReflectionParameter $parameter
     * @return null|string
     */
    private function extract_parameter_type(\ReflectionParameter $parameter): ?string
    {
        if (!$parameter->has_type()) {
            return null;
        }
        /** @var string|null $typeName */
        $type_name = null;
        $parameter_type = $parameter->get_type();
        if ($parameter_type instanceof \ReflectionUnionType) {
            $parameter_type = $parameter_type->get_types();
            $parameter_type = implode('|', $parameter_type);
        } elseif ($parameter_type instanceof \ReflectionIntersectionType) {
            $parameter_type = $parameter_type->get_types();
            $parameter_type = implode('&', $parameter_type);
        } else {
            $parameter_type = $parameter_type->get_name();
        }
        if ($parameter_type === 'array') {
            $type_name = 'array';
        } elseif ($parameter_class = $this->get_parameter_class($parameter)) {
            $type_name = $this->_get_fully_qualified_class_name($parameter_class->get_name());
        } elseif ($parameter_type === 'callable') {
            $type_name = 'callable';
        } else {
            $type_name = $parameter_type;
        }
        // Type "?array|string|null" is a union type, and therefore cannot be also marked nullable with the "?" prefix
        if ($parameter->allows_null() && $type_name !== 'mixed') {
            $type_name = str_contains($type_name, 'null') ? $type_name : '?' . $type_name;
        }
        return $type_name;
    }
    /**
     * Extract parameter default value
     *
     * @param \ReflectionParameter $parameter
     * @return null|ValueGenerator
     * @throws \ReflectionException
     */
    private function extract_parameter_default_value(\ReflectionParameter $parameter): ?Value_Generator
    {
        /** @var ValueGenerator|null $value */
        $value = null;
        if ($parameter->is_optional() && $parameter->is_default_value_available()) {
            $value_type = Value_Generator::TYPE_AUTO;
            $default_value = $parameter->get_default_value();
            if ($default_value === null) {
                $value_type = Value_Generator::TYPE_NULL;
            }
            $value = new Value_Generator($default_value, $value_type);
        }
        return $value;
    }
    /**
     * Retrieve method parameter info
     *
     * @param \ReflectionParameter $parameter
     * @return array
     * @throws \ReflectionException
     */
    protected function _get_method_parameter_info(\ReflectionParameter $parameter)
    {
        $parameter_info = ['name' => $parameter->get_name(), 'passedByReference' => $parameter->is_passed_by_reference()];
        if ($parameter->is_variadic()) {
            $parameter_info['variadic'] = $parameter->is_variadic();
        }
        if ($type = $this->extract_parameter_type($parameter)) {
            $parameter_info['type'] = $type;
        }
        if ($default = $this->extract_parameter_default_value($parameter)) {
            $parameter_info['defaultValue'] = $default;
        }
        return $parameter_info;
    }
    /**
     * Reinit generator
     *
     * @param string $sourceClassName
     * @param string $resultClassName
     * @return void
     */
    public function init($source_class_name, $result_class_name)
    {
        $this->_source_class_name = $source_class_name;
        $this->_result_class_name = $result_class_name;
    }
}