<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Query;

use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\DB\Select;
/**
 * Query batch iterator
 */
class Batch_Iterator implements Batch_Iterator_Interface
{
    /**
     * @var int
     */
    private $batch_size;
    /**
     * @var Select
     */
    private $select;
    /**
     * @var int
     */
    private $min_value = 0;
    /**
     * @var string
     */
    private $correlation_name;
    /**
     * @var string
     */
    private $range_field;
    /**
     * @var Select
     */
    private $current_select;
    /**
     * @var AdapterInterface
     */
    private $connection;
    /**
     * @var int
     */
    private $iteration = 0;
    /**
     * @var string
     */
    private $range_field_alias;
    /**
     * @var bool
     */
    private $is_valid = true;
    /**
     * Initialize dependencies.
     *
     * @param Select $select
     * @param int $batchSize
     * @param string $correlationName
     * @param string $rangeField
     * @param string $rangeFieldAlias
     */
    public function __construct(Select $select, $batch_size, $correlation_name, $range_field, $range_field_alias)
    {
        $this->batch_size = $batch_size;
        $this->select = $select;
        $this->correlation_name = $correlation_name;
        $this->range_field = $range_field;
        $this->range_field_alias = $range_field_alias;
        $this->connection = $select->get_connection();
    }
    /**
     * Returns current select
     *
     * @return Select
     */
    public function current()
    {
        if (null == $this->current_select) {
            $this->current_select = $this->init_select_object();
            $items_count = $this->calculate_batch_size($this->current_select);
            $this->is_valid = $items_count > 0;
        }
        return $this->current_select;
    }
    /**
     * Returns next select
     *
     * @return Select
     */
    public function next()
    {
        if (null == $this->current_select) {
            $this->current();
        }
        $select = $this->init_select_object();
        $items_count_in_select = $this->calculate_batch_size($select);
        $this->is_valid = $items_count_in_select > 0;
        if ($this->is_valid) {
            $this->iteration++;
            $this->current_select = $select;
        } else {
            $this->current_select = null;
        }
        return $this->current_select;
    }
    /**
     * Returns key
     *
     * @return int
     */
    public function key()
    {
        return $this->iteration;
    }
    /**
     * Returns is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->is_valid;
    }
    /**
     * Rewind
     *
     * @return void
     */
    public function rewind()
    {
        $this->min_value = 0;
        $this->current_select = null;
        $this->iteration = 0;
        $this->is_valid = true;
    }
    /**
     * Calculate batch size for select.
     *
     * @param Select $select
     * @return int
     */
    private function calculate_batch_size(Select $select)
    {
        $wrapper_select = $this->connection->select();
        $wrapper_select->from($select, [new \Zend_Db_Expr('MAX(' . $this->range_field_alias . ') as max'), new \Zend_Db_Expr('COUNT(*) as cnt')]);
        $row = $this->connection->fetch_row($wrapper_select);
        $this->min_value = $row['max'];
        return (int) $row['cnt'];
    }
    /**
     * Initialize select object.
     *
     * @return \Magento\Framework\DB\Select
     */
    private function init_select_object()
    {
        $object = clone $this->select;
        $object->where($this->connection->quote_identifier($this->correlation_name) . '.' . $this->connection->quote_identifier($this->range_field) . ' > ?', $this->min_value);
        $object->limit($this->batch_size);
        /**
         * Reset sort order section from origin select object
         */
        $object->order($this->correlation_name . '.' . $this->range_field . ' ' . \Magento\Framework\DB\Select::SQL_ASC);
        return $object;
    }
}