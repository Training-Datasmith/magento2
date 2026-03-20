<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\DB\Query;

/**
 * The batch queries iterator interface
 *
 * @api
 */
interface Batch_Iterator_Interface extends \Iterator
{
    /**
     * Constant which determine strategy to create iterator which will to process
     * range field eg. entity_id with unique values.
     */
    public const UNIQUE_FIELD_ITERATOR = 'unique';
    /**
     * Constant which determine strategy to create iterator which will to process
     * range field with non-unique values.
     */
    public const NON_UNIQUE_FIELD_ITERATOR = 'non_unqiue';
    /**
     * Return the current element
     *
     * If we don't have sub-select we should create and remember it.
     *
     * @return \Magento\Framework\DB\Select
     */
    #[\Return_Type_Will_Change]
    public function current();
    /**
     * Return the key of the current element
     *
     * Can return the number of the current sub-select in the iteration.
     *
     * @return int
     */
    #[\Return_Type_Will_Change]
    public function key();
    /**
     * Move forward to next sub-select
     *
     * Retrieve the next sub-select and move cursor to the next element.
     * Checks that the count of elements more than the sum of limit and offset.
     *
     * @return \Magento\Framework\DB\Select
     */
    #[\Return_Type_Will_Change]
    public function next();
    /**
     * Rewind the BatchRangeIterator to the first element.
     *
     * Allows to start iteration from the beginning.
     *
     * @return void
     */
    #[\Return_Type_Will_Change]
    public function rewind();
    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    #[\Return_Type_Will_Change]
    public function valid();
}