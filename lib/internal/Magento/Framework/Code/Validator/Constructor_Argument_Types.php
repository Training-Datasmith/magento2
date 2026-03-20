<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\Validator_Interface;
class Constructor_Argument_Types implements Validator_Interface
{
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $arguments_reader;
    /**
     * @var \Magento\Framework\Code\Reader\SourceArgumentsReader
     */
    protected $source_arguments_reader;
    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader
     * @param \Magento\Framework\Code\Reader\SourceArgumentsReader $sourceArgumentsReader
     */
    public function __construct(?\Magento\Framework\Code\Reader\Arguments_Reader $arguments_reader = null, ?\Magento\Framework\Code\Reader\Source_Arguments_Reader $source_arguments_reader = null)
    {
        $this->arguments_reader = $arguments_reader ?: new \Magento\Framework\Code\Reader\Arguments_Reader();
        $this->source_arguments_reader = $source_arguments_reader ?: new \Magento\Framework\Code\Reader\Source_Arguments_Reader();
    }
    /**
     * Validate class constructor arguments
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validate($class_name)
    {
        $class = new \ReflectionClass($class_name);
        $expected_arguments = $this->arguments_reader->get_constructor_arguments($class);
        $actual_arguments = array_filter($this->source_arguments_reader->get_constructor_argument_types($class));
        $expected_arguments = array_map(function ($element) {
            return $element['type'];
        }, $expected_arguments);
        foreach ($actual_arguments as $argument) {
            if (!in_array($argument, $expected_arguments)) {
                throw new \Magento\Framework\Exception\Validator_Exception(new \Magento\Framework\Phrase('Invalid constructor argument(s) in %1', [$class_name]));
            }
        }
        return true;
    }
}