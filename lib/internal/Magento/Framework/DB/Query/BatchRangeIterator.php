<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Query;

use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\DB\Select;
/**
 * Query batch range iterator
 *
 * It is uses to processing selects which will obtain values from  $rangeField with relation one-to-many
 * This iterator make chunks with operator LIMIT...OFFSET,
 * starting with zero offset and finishing on OFFSET + LIMIT = TOTAL_COUNT
 *
 * @see \Magento\Framework\DB\Query\Generator
 * @see \Magento\Framework\DB\Query\BatchIteratorFactory
 * @see \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
 * @see \Magento\Framework\DB\Adapter\Pdo\Mysql
 */
class Batch_Range_Iterator implements Batch_Iterator_Interface
{
    /**
     * @var Select
     */
    private $current_select;
    /**
     * @var string|array
     */
    private $range_field;
    /**
     * @var int
     */
    private $batch_size;
    /**
     * @var AdapterInterface
     */
    private $connection;
    /**
     * @var int
     */
    private $current_offset = 0;
    /**
     * @var int
     */
    private $total_item_count;
    /**
     * @var int
     */
    private $iteration = 0;
    /**
     * @var Select
     */
    private $select;
    /**
     * @var string
     */
    private $correlation_name;
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
     * @param string|array $rangeField
     * @param string $rangeFieldAlias @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Select $select, $batch_size, $correlation_name, $range_field, $range_field_alias = '')
    {
        $this->batch_size = $batch_size;
        $this->select = $select;
        $this->correlation_name = $correlation_name;
        $this->range_field = $range_field;
        $this->connection = $select->get_connection();
    }
    /**
     * Return the current element
     *
     * If we don't have sub-select we should create and remember it.
     *
     * @return Select
     */
    public function current()
    {
        if (null === $this->current_select) {
            $this->is_valid = $this->current_offset < $this->total_item_count;
            $this->current_select = $this->init_select_object();
        }
        return $this->current_select;
    }
    /**
     * Return the key of the current element
     *
     * Can return the number of the current sub-select in the iteration.
     *
     * @return int
     */
    public function key()
    {
        return $this->iteration;
    }
    /**
     * Move forward to next sub-select
     *
     * Retrieve the next sub-select and move cursor to the next element.
     * Checks that the count of elements more than the sum of limit and offset.
     *
     * @return Select
     */
    public function next()
    {
        if (null === $this->current_select) {
            $this->current();
        }
        $this->is_valid = $this->current_offset < $this->total_item_count;
        $select = $this->init_select_object();
        if ($this->is_valid) {
            $this->iteration++;
            $this->current_select = $select;
        } else {
            $this->current_select = null;
        }
        return $this->current_select;
    }
    /**
     * Rewind the BatchRangeIterator to the first element.
     *
     * Allows to start iteration from the beginning.
     *
     * @return void
     */
    public function rewind()
    {
        $this->current_select = null;
        $this->iteration = 0;
        $this->is_valid = true;
        $this->total_item_count = 0;
    }
    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->is_valid;
    }
    /**
     * Initialize select object
     *
     * Return sub-select which is limited by current batch value and return items from n page of SQL request.
     *
     * @return \Magento\Framework\DB\Select
     */
    private function init_select_object()
    {
        $object = clone $this->select;
        if (!$this->total_item_count) {
            $wrapper_select = $this->connection->select();
            $wrapper_select->from($object, [new \Zend_Db_Expr('COUNT(*) as cnt')]);
            $row = $this->connection->fetch_row($wrapper_select);
            $this->total_item_count = (int) $row['cnt'];
        }
        $range_field = is_array($this->range_field) ? $this->range_field : [$this->range_field];
        /**
         * Reset sort order section from origin select object
         */
        foreach ($range_field as $field) {
            $object->order($this->correlation_name . '.' . $field . ' ' . \Magento\Framework\DB\Select::SQL_ASC);
        }
        $object->limit($this->batch_size, $this->current_offset);
        $this->current_offset += $this->batch_size;
        return $object;
    }
}