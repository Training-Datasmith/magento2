<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data;

/**
 * Interface SearchResultProcessorInterface
 *
 * @api
 */
interface Search_Result_Processor_Interface
{
    /**
     * Retrieve all ids for collection
     *
     * @return array
     */
    public function get_all_ids();
    /**
     * Get current collection page
     *
     * @return int
     */
    public function get_current_page();
    /**
     * Retrieve collection page size
     *
     * @return int
     */
    public function get_page_size();
    /**
     * Retrieve collection first item
     *
     * @return \Magento\Framework\DataObject
     */
    public function get_first_item();
    /**
     * Retrieve collection last item
     *
     * @return \Magento\Framework\DataObject
     */
    public function get_last_item();
    /**
     * Retrieve field values from all items
     *
     * @param   string $colName
     * @return  array
     */
    public function get_column_values($col_name);
    /**
     * Search all items by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  array
     */
    public function get_items_by_column_value($column, $value);
    /**
     * Search first item by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  \Magento\Framework\DataObject || null
     */
    public function get_item_by_column_value($column, $value);
    /**
     * Retrieve item by id
     *
     * @param   mixed $idValue
     * @return  \Magento\Framework\DataObject
     */
    public function get_item_by_id($id_value);
    /**
     * Walk through the collection and run model method or external callback
     * with optional arguments
     *
     * Returns array with results of callback for each item
     *
     * @param string $callback
     * @param array $arguments
     * @return array
     */
    public function walk($callback, array $arguments = []);
    /**
     * Convert collection to XML
     *
     * @return string
     */
    public function to_xml();
    /**
     * Convert collection to array
     *
     * @param array $arrRequiredFields
     * @return array
     */
    public function to_array($arr_required_fields = []);
    /**
     * Convert items array to array for select options
     *
     * return items array
     * array(
     *      $index => array(
     *          'value' => mixed
     *          'label' => mixed
     *      )
     * )
     *
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    public function to_option_array($value_field = null, $label_field = null, $additional = []);
    /**
     * Convert items array to hash for select options
     *
     * return items hash
     * array($value => $label)
     *
     * @param   string $valueField
     * @param   string $labelField
     * @return  array
     */
    public function to_option_hash($value_field, $label_field);
}