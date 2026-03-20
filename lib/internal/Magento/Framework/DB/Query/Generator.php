<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Query;

use Magento\Framework\Exception\Localized_Exception;
/**
 * Query generator
 */
class Generator
{
    /**
     * @var \Magento\Framework\DB\Query\BatchIteratorFactory
     */
    private $iterator_factory;
    /**
     * @var \Magento\Framework\DB\Query\BatchRangeIteratorFactory
     */
    private $range_iterator_factory;
    /**
     * Initialize dependencies.
     *
     * @param BatchIteratorFactory $iteratorFactory
     * @param BatchRangeIteratorFactory $rangeIteratorFactory
     */
    public function __construct(Batch_Iterator_Factory $iterator_factory, ?Batch_Range_Iterator_Factory $range_iterator_factory = null)
    {
        $this->iterator_factory = $iterator_factory;
        $this->range_iterator_factory = $range_iterator_factory ?: \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Framework\DB\Query\Batch_Range_Iterator_Factory::class);
    }
    /**
     * Generate select query list with predefined items count in each select item
     *
     * Generates select parameters - batchSize, correlationName, rangeField, rangeFieldAlias
     * to obtain instance of iterator. The behavior of the iterator will depend on the parameters passed to it.
     * For example: by default for $batchStrategy parameter used
     * \Magento\Framework\DB\Query\BatchIteratorInterface::UNIQUE_FIELD_ITERATOR. This parameter is determine, what
     * instance of Iterator will be returned.
     *
     * Other params:
     * select - represents the select object, that should be passed into Iterator.
     * batchSize - sets the number of items in select.
     * correlationName - is the base table involved in the select.
     * rangeField - this is the basic field which used to split select.
     * rangeFieldAlias - alias of range field.
     *
     * @see \Magento\Framework\DB\Query\BatchIteratorInterface
     * @param string $rangeField -  Field which is used for the range mechanism in select
     * @param \Magento\Framework\DB\Select $select
     * @param int $batchSize - Determines on how many parts will be divided
     * the number of values in the select.
     * @param string $batchStrategy It determines which strategy is chosen
     * @return BatchIteratorInterface
     * @throws LocalizedException Throws if incorrect "FROM" part in \Select exists
     */
    public function generate($range_field, \Magento\Framework\DB\Select $select, $batch_size = 100, $batch_strategy = \Magento\Framework\DB\Query\Batch_Iterator_Interface::UNIQUE_FIELD_ITERATOR)
    {
        if ($batch_strategy == \Magento\Framework\DB\Query\Batch_Iterator_Interface::NON_UNIQUE_FIELD_ITERATOR) {
            return $this->generate_by_range($range_field, $select, $batch_size);
        }
        $from_select = $select->get_part(\Magento\Framework\DB\Select::FROM);
        if (empty($from_select)) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The select object must have the correct "FROM" part. Verify and try again.'));
        }
        $field_correlation_name = '';
        foreach ($from_select as $correlation_name => $from_part) {
            if ($from_part['joinType'] == \Magento\Framework\DB\Select::FROM) {
                $field_correlation_name = $correlation_name;
                break;
            }
        }
        $columns = $select->get_part(\Magento\Framework\DB\Select::COLUMNS);
        /**
         * Calculate $rangeField alias
         */
        $range_field_alias = $range_field;
        foreach ($columns as $column) {
            list($table, $column_name, $alias) = $column;
            if ($table == $field_correlation_name && $column_name == $range_field) {
                $range_field_alias = $alias ?: $range_field;
                break;
            }
        }
        return $this->iterator_factory->create(['select' => $select, 'batchSize' => $batch_size, 'correlationName' => $field_correlation_name, 'rangeField' => $range_field, 'rangeFieldAlias' => $range_field_alias]);
    }
    /**
     * Generate select query list with predefined items count in each select item.
     *
     * Generates select parameters - batchSize, correlationName, rangeField, rangeFieldAlias
     * to obtain instance of BatchRangeIterator.
     *
     * Other params:
     * select - represents the select object, that should be passed into Iterator.
     * batchSize - sets the number of items in select.
     * correlationName - is the base table involved in the select.
     * rangeField - this is the basic field which used to split select.
     * rangeFieldAlias - alias of range field.
     *
     * @see BatchRangeIterator
     * @param string $rangeField -  Field which is used for the range mechanism in select
     * @param \Magento\Framework\DB\Select $select
     * @param int $batchSize
     * @return BatchIteratorInterface
     * @throws LocalizedException Throws if incorrect "FROM" part in \Select exists
     * @see \Magento\Framework\DB\Query\Generator
     * @deprecated 100.1.8 This is a temporary solution which is made due to the fact that we
     *             can't change method generate() in version 2.1 due to a backwards incompatibility.
     *             In 2.2 version need to use original method generate() with additional parameter.
     */
    public function generate_by_range($range_field, \Magento\Framework\DB\Select $select, $batch_size = 100)
    {
        $from_select = $select->get_part(\Magento\Framework\DB\Select::FROM);
        if (empty($from_select)) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The select object must have the correct "FROM" part. Verify and try again.'));
        }
        $field_correlation_name = '';
        foreach ($from_select as $correlation_name => $from_part) {
            if ($from_part['joinType'] == \Magento\Framework\DB\Select::FROM) {
                $field_correlation_name = $correlation_name;
                break;
            }
        }
        $columns = $select->get_part(\Magento\Framework\DB\Select::COLUMNS);
        /**
         * Calculate $rangeField alias
         */
        $range_field_alias = $range_field;
        foreach ($columns as $column) {
            list($table, $column_name, $alias) = $column;
            if ($table == $field_correlation_name && $column_name == $range_field) {
                $range_field_alias = $alias ?: $range_field;
                break;
            }
        }
        return $this->range_iterator_factory->create(['select' => $select, 'batchSize' => $batch_size, 'correlationName' => $field_correlation_name, 'rangeField' => $range_field, 'rangeFieldAlias' => $range_field_alias]);
    }
}