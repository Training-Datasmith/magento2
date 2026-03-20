<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\Code_Generator_Interface;
use Magento\Framework\Code\Generator\Defined_Classes;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Object_Manager\Code\Generator\Factory;
class Extension_Attributes_Interface_Factory_Generator extends Factory
{
    /**
     * {@inheritdoc}
     */
    public const ENTITY_TYPE = 'extensionInterfaceFactory';
    /**
     * Initialize dependencies.
     *
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io $ioObject
     * @param CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     */
    public function __construct($source_class_name = null, $result_class_name = null, ?Io $io_object = null, ?Code_Generator_Interface $class_generator = null, ?Defined_Classes $defined_classes = null)
    {
        $source_class_name .= 'Extension';
        parent::__construct($source_class_name, $result_class_name, $io_object, $class_generator, $defined_classes);
    }
    /**
     * @inheritdoc
     */
    protected function get_result_class_suffix()
    {
        return 'InterfaceFactory';
    }
}