<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

/**
 * Class SearchResultProcessor
 */
class Search_Result_Processor extends Abstract_Data_Object implements Search_Result_Processor_Interface
{
    /**
     * Data Interface name
     *
     * @var string
     */
    protected $data_interface = \Magento\Framework\Data_Object::class;
    /**
     * @var AbstractSearchResult
     */
    protected $search_result;
    /**
     * @param AbstractSearchResult $searchResult
     */
    public function __construct(Abstract_Search_Result $search_result)
    {
        $this->search_result = $search_result;
    }
    /**
     * @return int
     */
    public function get_current_page()
    {
        return $this->search_result->get_search_criteria()->get_limit()[0];
    }
    /**
     * @return int
     */
    public function get_page_size()
    {
        return $this->search_result->get_search_criteria()->get_limit()[1];
    }
    /**
     * @return \Magento\Framework\DataObject|mixed
     */
    public function get_first_item()
    {
        return current($this->search_result->get_items());
    }
    /**
     * @return \Magento\Framework\DataObject|mixed
     */
    public function get_last_item()
    {
        $items = $this->search_result->get_items();
        return end($items);
    }
    /**
     * @return array
     */
    public function get_all_ids()
    {
        $ids = [];
        foreach ($this->search_result->get_items() as $item) {
            $ids[] = $this->search_result->get_item_id($item);
        }
        return $ids;
    }
    /**
     * @param int $id
     * @return \Magento\Framework\DataObject|null
     */
    public function get_item_by_id($id)
    {
        $items = $this->search_result->get_items();
        if (isset($items[$id])) {
            return $items[$id];
        }
        return null;
    }
    /**
     * @param string $colName
     * @return array
     */
    public function get_column_values($col_name)
    {
        $col = [];
        foreach ($this->search_result->get_items() as $item) {
            $col[] = $item->get_data($col_name);
        }
        return $col;
    }
    /**
     * @param string $column
     * @param mixed $value
     * @return array
     */
    public function get_items_by_column_value($column, $value)
    {
        $res = [];
        foreach ($this->search_result->get_items() as $item) {
            if ($item->get_data($column) == $value) {
                $res[] = $item;
            }
        }
        return $res;
    }
    /**
     * @param string $column
     * @param mixed $value
     * @return \Magento\Framework\DataObject|null
     */
    public function get_item_by_column_value($column, $value)
    {
        foreach ($this->search_result->get_items() as $item) {
            if ($item->get_data($column) == $value) {
                return $item;
            }
        }
        return null;
    }
    /**
     * @param string $callback
     * @param array $args
     * @return array
     */
    public function walk($callback, array $args = [])
    {
        $results = [];
        $use_item_callback = is_string($callback) && strpos($callback, '::') === false;
        foreach ($this->search_result->get_items() as $id => $item) {
            if ($use_item_callback) {
                $cb = [$item, $callback];
            } else {
                $cb = $callback;
                array_unshift($args, $item);
            }
            $results[$id] = call_user_func_array($cb, $args);
        }
        return $results;
    }
    /**
     * @return string
     */
    public function to_xml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <collection>
           <totalRecords>' . $this->search_result->get_size() . '</totalRecords>
           <items>';
        foreach ($this->search_result->get_items() as $item) {
            $xml .= $item->to_xml();
        }
        $xml .= '</items>
        </collection>';
        return $xml;
    }
    /**
     * @param array $arrRequiredFields
     * @return array
     */
    public function to_array($arr_required_fields = [])
    {
        $array = [];
        $array['search_criteria'] = $this->search_result->get_search_criteria();
        $array['total_count'] = $this->search_result->get_total_count();
        foreach ($this->search_result->get_items() as $item) {
            $array['items'][] = $item->to_array($arr_required_fields);
        }
        return $array;
    }
    /**
     * @param string|null $valueField
     * @param string|null $labelField
     * @param array $additional
     * @return array
     */
    public function to_option_array($value_field = null, $label_field = null, $additional = [])
    {
        if ($value_field === null) {
            $value_field = $this->search_result->get_id_field_name();
        }
        if ($label_field === null) {
            $label_field = 'name';
        }
        $result = [];
        $additional['value'] = $value_field;
        $additional['label'] = $label_field;
        foreach ($this->search_result->get_items() as $item) {
            $data = [];
            foreach ($additional as $code => $field) {
                $data[$code] = $item->get_data($field);
            }
            $result[] = $data;
        }
        return $result;
    }
    /**
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    public function to_option_hash($value_field, $label_field)
    {
        $res = [];
        foreach ($this->search_result->get_items() as $item) {
            $res[$item->get_data($value_field)] = $item->get_data($label_field);
        }
        return $res;
    }
    /**
     * @return string
     */
    protected function get_data_interface_name()
    {
        return $this->data_interface;
    }
}