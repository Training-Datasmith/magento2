<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\Defined_Classes;
use Magento\Framework\Code\Generator\Io;
/**
 * Code generator for data object extension interfaces.
 */
class Extension_Attributes_Interface_Generator extends \Magento\Framework\Api\Code\Generator\Extension_Attributes_Generator
{
    public const ENTITY_TYPE = 'extensionInterface';
    public const EXTENSION_INTERFACE_SUFFIX = 'ExtensionInterface';
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
        if (!$class_generator) {
            $class_generator = new \Magento\Framework\Code\Generator\Interface_Generator();
        }
        parent::__construct($config, $source_class_name, $result_class_name, $io_object, $class_generator, $defined_classes);
    }
    /**
     * {@inheritdoc}
     */
    protected function get_extended_class()
    {
        return '\\' . \Magento\Framework\Api\Extension_Attributes_Interface::class;
    }
    /**
     * {@inheritdoc}
     */
    protected function validate_result_class_name()
    {
        $result = true;
        $source_class_name = $this->get_source_class_name();
        $result_class_name = $this->_get_result_class_name();
        $interface_suffix = 'Interface';
        $expected_result_class_name = substr($source_class_name, 0, -strlen($interface_suffix)) . self::EXTENSION_INTERFACE_SUFFIX;
        if ($result_class_name !== $expected_result_class_name) {
            $this->_add_error('Invalid extension interface name [' . $result_class_name . ']. Use ' . $expected_result_class_name);
            $result = false;
        }
        return $result;
    }
}