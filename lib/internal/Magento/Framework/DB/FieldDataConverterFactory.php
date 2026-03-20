<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\Object_Manager_Interface;
/**
 * Create instance of FieldDataConverter with concrete implementation of DataConverterInterface
 */
class Field_Data_Converter_Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create instance of FieldDataConverter
     *
     * @param string $dataConverterClassName
     * @return FieldDataConverter
     */
    public function create($data_converter_class_name)
    {
        return $this->object_manager->create(Field_Data_Converter::class, ['dataConverter' => $this->object_manager->get($data_converter_class_name)]);
    }
}