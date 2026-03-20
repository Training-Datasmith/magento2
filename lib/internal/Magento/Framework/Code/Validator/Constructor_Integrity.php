<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\Validator_Interface;
use Magento\Framework\Exception\Validator_Exception;
use Magento\Framework\Phrase;
/**
 * Class constructor validator. Validates call of parent construct
 */
class Constructor_Integrity implements Validator_Interface
{
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_arguments_reader;
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
     * @throws ValidatorException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate($class_name)
    {
        $class = new \ReflectionClass($class_name);
        $parent = $class->get_parent_class();
        /** Check whether parent class exists and has __construct method */
        if (!$parent) {
            return true;
        }
        /** Get parent class __construct arguments */
        $parent_arguments = $this->_arguments_reader->get_constructor_arguments($parent, true, true);
        if (empty($parent_arguments)) {
            return true;
        }
        /** Check whether class has __construct */
        $class_arguments = $this->_arguments_reader->get_constructor_arguments($class);
        if (null === $class_arguments) {
            return true;
        }
        /** Check whether class has parent::__construct call */
        $call_arguments = $this->_arguments_reader->get_parent_call($class, $class_arguments);
        if (null === $call_arguments) {
            return true;
        }
        /** Get parent class __construct arguments */
        $parent_arguments = $this->_arguments_reader->get_constructor_arguments($parent, true, true);
        foreach ($parent_arguments as $index => $required_argument) {
            $re_indexed_call_arguments = array_column($call_arguments, null, 'name');
            if (isset($re_indexed_call_arguments[$required_argument['name']])) {
                if ($re_indexed_call_arguments[$required_argument['name']]['isNamedArgument'] === true) {
                    $actual_argument = $re_indexed_call_arguments[$required_argument['name']];
                    $this->check_compatible_types($required_argument['type'], $actual_argument['type'], $class);
                    continue;
                }
            }
            if (isset($call_arguments[$index]) && $call_arguments[$index]['isNamedArgument'] === true) {
                $this->check_if_required_argument_is_optional($required_argument, $class);
            }
            if (isset($call_arguments[$index])) {
                $actual_argument = $call_arguments[$index];
                $this->check_compatible_types($required_argument['type'], $actual_argument['type'], $class);
            } else {
                $this->check_if_required_argument_is_optional($required_argument, $class);
            }
        }
        return true;
    }
    /**
     * Check argument type compatibility
     *
     * @param string $requiredArgumentType
     * @param string $actualArgumentType
     * @param \ReflectionClass $class
     * @return void
     * @throws ValidatorException
     */
    private function check_compatible_types($required_argument_type, $actual_argument_type, \ReflectionClass $class): void
    {
        $is_compatible_types = $this->_arguments_reader->is_compatible_type($required_argument_type, $actual_argument_type);
        if (!$is_compatible_types) {
            $class_path = str_replace('\\', '/', $class->get_file_name());
            throw new Validator_Exception(new Phrase('Incompatible argument type: Required type: %1. Actual type: %2; File: %3%4%5', [$required_argument_type, $actual_argument_type, PHP_EOL, $class_path, PHP_EOL]));
        }
    }
    /**
     * Check if required argument is optional
     *
     * @param array $requiredArgument
     * @param \ReflectionClass $class
     * @return void
     * @throws ValidatorException
     */
    private function check_if_required_argument_is_optional(array $required_argument, \ReflectionClass $class): void
    {
        if (!$required_argument['isOptional']) {
            $class_path = str_replace('\\', '/', $class->get_file_name());
            throw new Validator_Exception(new Phrase('Missed required argument %1 in parent::__construct call. File: %2', [$required_argument['name'], $class_path]));
        }
    }
}