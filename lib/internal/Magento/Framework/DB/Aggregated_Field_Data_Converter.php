<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\Adapter_Interface;
/**
 * Date converter for multiple fields in different tables using different field converters
 */
class Aggregated_Field_Data_Converter
{
    /**
     * @var FieldDataConverterFactory
     */
    private $field_data_converter_factory;
    /**
     * @var FieldDataConverter[]
     */
    private $field_data_converters = [];
    /**
     * Constructor
     *
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(Field_Data_Converter_Factory $field_data_converter_factory)
    {
        $this->field_data_converter_factory = $field_data_converter_factory;
    }
    /**
     * Convert data for the specified fields using specified field converters
     *
     * @param FieldToConvert[] $fieldsToUpdate
     * @param AdapterInterface $connection
     * @throws FieldDataConversionException
     * @return void
     */
    public function convert(array $fields_to_update, Adapter_Interface $connection)
    {
        foreach ($fields_to_update as $field) {
            $field_data_converter = $this->get_field_data_converter($field->get_data_converter_class());
            $field_data_converter->convert($connection, $field->get_table_name(), $field->get_identifier_field(), $field->get_field_name(), $field->get_query_modifier());
        }
    }
    /**
     * Get field data converter
     *
     * @param string $dataConverterClassName
     * @return FieldDataConverter
     */
    private function get_field_data_converter($data_converter_class_name)
    {
        if (!isset($this->field_data_converters[$data_converter_class_name])) {
            $this->field_data_converters[$data_converter_class_name] = $this->field_data_converter_factory->create($data_converter_class_name);
        }
        return $this->field_data_converters[$data_converter_class_name];
    }
}