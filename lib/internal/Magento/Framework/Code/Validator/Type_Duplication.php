<?php

declare (strict_types=1);
/**
 * Class constructor validator. Validates argument types duplication
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\Validator_Interface;
class Type_Duplication implements Validator_Interface
{
    /**
     * Name of the suppress warnings annotation.
     */
    public const SUPPRESS_ANNOTATION = 'SuppressWarnings';
    public const TYPE_DUPLICATIONS = 'Magento.TypeDuplication';
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_arguments_reader;
    /**
     * @var \Magento\Framework\Code\Reader\ScalarTypesProvider
     */
    private $scalar_types_provider;
    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader|null $argumentsReader
     * @param \Magento\Framework\Code\Reader\ScalarTypesProvider|null $scalarTypesProvider
     */
    public function __construct(?\Magento\Framework\Code\Reader\Arguments_Reader $arguments_reader = null, ?\Magento\Framework\Code\Reader\Scalar_Types_Provider $scalar_types_provider = null)
    {
        $this->_arguments_reader = $arguments_reader ?: new \Magento\Framework\Code\Reader\Arguments_Reader();
        $this->scalar_types_provider = $scalar_types_provider ?: new \Magento\Framework\Code\Reader\Scalar_Types_Provider();
    }
    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validate($class_name)
    {
        $class = new \ReflectionClass($class_name);
        $class_arguments = $this->_arguments_reader->get_constructor_arguments($class, true);
        $arguments = $this->_get_object_arguments($class_arguments);
        $type_list = [];
        $errors = [];
        foreach ($arguments as $argument) {
            $name = $argument['name'];
            $type = $argument['type'];
            if (in_array($type, $type_list)) {
                $errors[] = 'Multiple type injection [' . $type . ']';
            } elseif (isset($type_list[$name])) {
                $errors[] = 'Variable name duplication. [$' . $name . ']';
            }
            $type_list[$name] = $type;
        }
        if (!empty($errors)) {
            if (false == $this->_ignore_warning($class)) {
                $class_path = str_replace('\\', '/', $class->get_file_name());
                throw new \Magento\Framework\Exception\Validator_Exception(new \Magento\Framework\Phrase('Argument type duplication in class %1 in %2%3%4', [$class->get_name(), $class_path, PHP_EOL, implode(PHP_EOL, $errors)]));
            }
        }
        return true;
    }
    /**
     * Get arguments with object types
     *
     * @param array $arguments
     * @return array
     */
    protected function _get_object_arguments(array $arguments)
    {
        $output = [];
        foreach ($arguments as $argument) {
            $type = $argument['type'];
            if (!$type || in_array($type, $this->scalar_types_provider->get_types())) {
                continue;
            }
            $reflection = new \ReflectionClass($type);
            if (false == $reflection->is_interface()) {
                $output[] = $argument;
            }
        }
        return $output;
    }
    /**
     * Check whether warning must be skipped
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    protected function _ignore_warning(\ReflectionClass $class)
    {
        $annotations = $this->_arguments_reader->get_annotations($class);
        if (isset($annotations[self::SUPPRESS_ANNOTATION])) {
            return $annotations[self::SUPPRESS_ANNOTATION] == self::TYPE_DUPLICATIONS;
        }
        return false;
    }
}