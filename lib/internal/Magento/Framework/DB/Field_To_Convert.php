<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\DB\Select\Query_Modifier_Interface;
/**
 * Value object for information about a field to be converted
 */
class Field_To_Convert
{
    /**
     * @var string
     */
    private $data_converter_class;
    /**
     * @var string
     */
    private $table_name;
    /**
     * @var string
     */
    private $identifier_field;
    /**
     * @var string
     */
    private $field_name;
    /**
     * @var QueryModifierInterface|null
     */
    private $query_modifier;
    /**
     * FieldToConvert constructor
     *
     * @param string $dataConverter
     * @param string $table
     * @param string $identifierField
     * @param string $fieldName
     * @param QueryModifierInterface $queryModifier
     */
    public function __construct($data_converter, $table, $identifier_field, $field_name, ?Query_Modifier_Interface $query_modifier = null)
    {
        $this->data_converter_class = $data_converter;
        $this->table_name = $table;
        $this->field_name = $field_name;
        $this->identifier_field = $identifier_field;
        $this->query_modifier = $query_modifier;
    }
    /**
     * Get data converter class name
     *
     * @return string
     */
    public function get_data_converter_class()
    {
        return $this->data_converter_class;
    }
    /**
     * Get table name
     *
     * @return string
     */
    public function get_table_name()
    {
        return $this->table_name;
    }
    /**
     * Get ID field name
     *
     * @return string
     */
    public function get_identifier_field()
    {
        return $this->identifier_field;
    }
    /**
     * Get field name
     *
     * @return string
     */
    public function get_field_name()
    {
        return $this->field_name;
    }
    /**
     * Get query modifier
     *
     * @return QueryModifierInterface|null
     */
    public function get_query_modifier()
    {
        return $this->query_modifier;
    }
}