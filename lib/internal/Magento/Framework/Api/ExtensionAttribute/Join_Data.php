<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Extension_Attribute;

/**
 * Data holder for extension attribute joins.
 *
 * @api
 * @codeCoverageIgnore
 */
class Join_Data implements Join_Data_Interface
{
    /**
     * @var string
     */
    private $attribute_code;
    /**
     * @var string
     */
    private $reference_table;
    /**
     * @var string
     */
    private $reference_table_alias;
    /**
     * @var string
     */
    private $reference_field;
    /**
     * @var string
     */
    private $join_field;
    /**
     * @var string[]
     */
    private $select_fields;
    /**
     * {@inheritdoc}
     */
    public function get_attribute_code()
    {
        return $this->attribute_code;
    }
    /**
     * {@inheritdoc}
     */
    public function set_attribute_code($attribute_code)
    {
        $this->attribute_code = $attribute_code;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function get_reference_table()
    {
        return $this->reference_table;
    }
    /**
     * {@inheritdoc}
     */
    public function set_reference_table($reference_table)
    {
        $this->reference_table = $reference_table;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function get_reference_table_alias()
    {
        return $this->reference_table_alias;
    }
    /**
     * {@inheritdoc}
     */
    public function set_reference_table_alias($reference_table_alias)
    {
        $this->reference_table_alias = $reference_table_alias;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function get_reference_field()
    {
        return $this->reference_field;
    }
    /**
     * {@inheritdoc}
     */
    public function set_reference_field($reference_field)
    {
        $this->reference_field = $reference_field;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function get_join_field()
    {
        return $this->join_field;
    }
    /**
     * {@inheritdoc}
     */
    public function set_join_field($join_field)
    {
        $this->join_field = $join_field;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function get_select_fields()
    {
        return $this->select_fields;
    }
    /**
     * {@inheritdoc}
     */
    public function set_select_fields(array $select_fields)
    {
        $this->select_fields = $select_fields;
        return $this;
    }
}