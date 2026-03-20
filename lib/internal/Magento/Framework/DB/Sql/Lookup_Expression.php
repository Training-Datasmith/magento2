<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Sql;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\DB\Select;
/**
 * Class LookupExpression
 */
class Lookup_Expression extends Expression
{
    /**
     * @var Resource
     */
    protected $resource;
    /**
     * @var AdapterInterface
     */
    protected $adapter;
    /**
     * @var string
     */
    protected $target_column;
    /**
     * @var string
     */
    protected $target_table;
    /**
     * @var array
     */
    protected $reference_columns;
    /**
     * @var array
     */
    protected $sort_order;
    /**
     * @param ResourceConnection $resource
     * @param string $targetColumn
     * @param string $targetTable
     * @param array $referenceColumns
     * @param array $sortOrder
     */
    public function __construct(Resource_Connection $resource, $target_column, $target_table, array $reference_columns = [], array $sort_order = [])
    {
        $this->target_table = $target_table;
        $this->target_column = $target_column;
        $this->reference_columns = $reference_columns;
        $this->sort_order = $sort_order;
        $this->resource = $resource;
        $this->adapter = $this->resource->get_connection();
    }
    /**
     * Process WHERE clause
     *
     * @param Select $select
     * @return void
     */
    protected function process_where_condition(Select $select)
    {
        foreach ($this->reference_columns as $column => $reference_column) {
            $identifier = '';
            if (isset($reference_column['tableAlias'])) {
                $identifier = $reference_column['tableAlias'] . '.';
            }
            $column_name = $column;
            if (isset($reference_column['columnName'])) {
                $column_name = $reference_column['columnName'];
            }
            $select->where(sprintf('%s = %s', $this->adapter->quote_identifier('lookup.' . $column), $this->adapter->quote_identifier($identifier . $column_name)));
        }
    }
    /**
     * Process ORDER BY clause
     *
     * @param Select $select
     * @return void
     */
    protected function process_sort_order(Select $select)
    {
        foreach ($this->sort_order as $direction => $column) {
            if (!in_array($direction, [Select::SQL_ASC, Select::SQL_DESC])) {
                $direction = '';
            }
            $expr = new \Zend_Db_Expr(sprintf('%s %s', $this->adapter->quote_identifier('lookup.' . $column), $direction));
            $select->order($expr);
        }
    }
    /**
     * Returns lookup SQL
     *
     * @return string
     */
    public function __toString()
    {
        $select = $this->adapter->select()->from(['lookup' => $this->resource->get_table_name($this->target_table)], [$this->target_column])->limit(1);
        $this->process_where_condition($select);
        $this->process_sort_order($select);
        return sprintf('(%s)', $select->assemble());
    }
}