<?php

declare (strict_types=1);
/**
 * Class constructor validator. Validates arguments sequence
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\Validator_Interface;
class Argument_Sequence implements Validator_Interface
{
    public const REQUIRED = 'required';
    public const OPTIONAL = 'optional';
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_arguments_reader;
    /**
     * @var array
     */
    protected $_cache;
    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader
     */
    public function __construct(?\Magento\Framework\Code\Reader\Arguments_Reader $arguments_reader = null)
    {
        $this->_arguments_reader = $arguments_reader ?: new \Magento\Framework\Code\Reader\Arguments_Reader();
    }
    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate($class_name)
    {
        $class = new \ReflectionClass($class_name);
        $class_arguments = $this->_arguments_reader->get_constructor_arguments($class);
        if ($this->_is_context_only($class_arguments)) {
            return true;
        }
        $parent = $class->get_parent_class();
        $parent_arguments = [];
        if ($parent) {
            $parent_class = $parent->get_name();
            if (0 !== strpos($parent_class, '\\')) {
                $parent_class = '\\' . $parent_class;
            }
            if (isset($this->_cache[$parent_class])) {
                $parent_call = $this->_arguments_reader->get_parent_call($class, []);
                if (empty($class_arguments) || $parent_call) {
                    $parent_arguments = $this->_cache[$parent_class];
                }
            }
        }
        if (empty($class_arguments)) {
            $class_arguments = $parent_arguments;
        }
        $required_sequence = $this->_builds_sequence($class_arguments, $parent_arguments);
        if (!empty($required_sequence)) {
            $this->_cache[$class_name] = $required_sequence;
        }
        if (false == $this->_check_argument_sequence($class_arguments, $required_sequence)) {
            $class_path = str_replace('\\', '/', $class->get_file_name());
            throw new \Magento\Framework\Exception\Validator_Exception(new \Magento\Framework\Phrase('Incorrect argument sequence in class %1 in %2%3Required: $%4%5Actual  : $%6%7', [$class_name, $class_path, PHP_EOL, implode(', $', array_keys($required_sequence)), PHP_EOL, implode(', $', array_keys($class_arguments)), PHP_EOL]));
        }
        return true;
    }
    /**
     * Check argument sequence
     *
     * @param array $actualSequence
     * @param array $requiredSequence
     * @return bool
     */
    protected function _check_argument_sequence(array $actual_sequence, array $required_sequence)
    {
        $actual_argument_sequence = [];
        $required_argument_sequence = [];
        foreach ($actual_sequence as $name => $argument) {
            if (false == $argument['isOptional']) {
                $actual_argument_sequence[$name] = $argument;
            } else {
                break;
            }
        }
        foreach ($required_sequence as $name => $argument) {
            if (false == $argument['isOptional']) {
                $required_argument_sequence[$name] = $argument;
            } else {
                break;
            }
        }
        $actual = array_keys($actual_argument_sequence);
        $required = array_keys($required_argument_sequence);
        return $actual === $required;
    }
    /**
     * Build argument required sequence
     *
     * @param array $classArguments
     * @param array $parentArguments
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _builds_sequence(array $class_arguments, array $parent_arguments = [])
    {
        $output = [];
        if (empty($class_arguments)) {
            return $parent_arguments;
        }
        $class_argument_list = $this->_sort_arguments($class_arguments);
        $parent_argument_list = $this->_sort_arguments($parent_arguments);
        $migrated = [];
        foreach ($parent_argument_list[self::REQUIRED] as $name => $argument) {
            if (!isset($class_argument_list[self::OPTIONAL][$name])) {
                $output[$name] = isset($class_argument_list[self::REQUIRED][$name]) ? $class_argument_list[self::REQUIRED][$name] : $argument;
            } else {
                $migrated[$name] = $class_argument_list[self::OPTIONAL][$name];
            }
        }
        foreach ($class_argument_list[self::REQUIRED] as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = $argument;
            }
        }
        /** Use parent required argument that become optional in child class */
        foreach ($migrated as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = $argument;
            }
        }
        foreach ($parent_argument_list[self::OPTIONAL] as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = isset($class_argument_list[self::OPTIONAL][$name]) ? $class_argument_list[self::OPTIONAL][$name] : $argument;
            }
        }
        foreach ($class_argument_list[self::OPTIONAL] as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = $argument;
            }
        }
        return $output;
    }
    /**
     * Sort arguments
     *
     * @param array $arguments
     * @return array
     */
    protected function _sort_arguments($arguments)
    {
        $required = [];
        $optional = [];
        foreach ($arguments as $name => $argument) {
            if ($argument['isOptional']) {
                $optional[$name] = $argument;
            } else {
                $required[$name] = $argument;
            }
        }
        return [self::REQUIRED => $required, self::OPTIONAL => $optional];
    }
    /**
     * Check whether arguments list contains an only context argument
     *
     * @param array $arguments
     * @return bool
     */
    protected function _is_context_only(array $arguments)
    {
        if (count($arguments) !== 1) {
            return false;
        }
        $argument = current($arguments);
        return $argument['type'] && $this->_is_context_type($argument['type']);
    }
    /**
     * Check whether type is context object
     *
     * @param string $type
     * @return bool
     */
    protected function _is_context_type($type)
    {
        return is_subclass_of($type, \Magento\Framework\Object_Manager\Context_Interface::class);
    }
}