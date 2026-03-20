<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Extension_Attribute;

/**
 * Interface of data holder for extension attribute joins.
 *
 * @api
 * @since 100.0.2
 */
interface Join_Data_Interface
{
    public const SELECT_FIELD_EXTERNAL_ALIAS = 'external_alias';
    public const SELECT_FIELD_INTERNAL_ALIAS = 'internal_alias';
    public const SELECT_FIELD_WITH_DB_PREFIX = 'with_db_prefix';
    public const SELECT_FIELD_SETTER = 'setter';
    /**
     * Get attribute code.
     *
     * @return string
     */
    public function get_attribute_code();
    /**
     * Set attribute code.
     *
     * @param string $attributeCode
     * @return $this
     */
    public function set_attribute_code($attribute_code);
    /**
     * Get reference table name.
     *
     * @return string
     */
    public function get_reference_table();
    /**
     * Set reference table name.
     *
     * @param string $referenceTable
     * @return $this
     */
    public function set_reference_table($reference_table);
    /**
     * Get reference table alias.
     *
     * @return string
     */
    public function get_reference_table_alias();
    /**
     * Set reference table alias.
     *
     * @param string $referenceTableAlias
     * @return $this
     */
    public function set_reference_table_alias($reference_table_alias);
    /**
     * Get reference field.
     *
     * @return string
     */
    public function get_reference_field();
    /**
     * Set reference field.
     *
     * @param string $referenceField
     * @return $this
     */
    public function set_reference_field($reference_field);
    /**
     * Get join field.
     *
     * @return string
     */
    public function get_join_field();
    /**
     * Set join field.
     *
     * @param string $joinField
     * @return $this
     */
    public function set_join_field($join_field);
    /**
     * Get select fields.
     *
     * @return array
     */
    public function get_select_fields();
    /**
     * Set select field.
     *
     * @param array $selectFields
     * @return $this
     */
    public function set_select_fields(array $select_fields);
}