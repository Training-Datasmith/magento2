<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Resource_Connection;

/**
 * @api
 * @since 100.0.2
 */
interface Source_Provider_Interface extends \Traversable
{
    /**
     * Returns main table name - extracted from "module/table" style and
     * validated by db adapter
     *
     * @return string
     */
    public function get_main_table();
    /**
     * Get primary key field name
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return string
     */
    public function get_id_field_name();
    /**
     * @param string $fieldName
     * @param null|string $alias
     * @return $this
     */
    public function add_field_to_select($field_name, $alias = null);
    /**
     * Get \Magento\Framework\DB\Select instance and applies fields to select if needed
     *
     * @return \Magento\Framework\DB\Select
     */
    public function get_select();
    /**
     * Wrapper for compatibility with \Magento\Framework\Data\Collection\AbstractDb
     *
     * @param mixed $attribute
     * @param mixed $condition
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function add_field_to_filter($attribute, $condition = null);
}