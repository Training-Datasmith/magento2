<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

/**
 * DataBase Helper
 */
class Helper extends \Magento\Framework\DB\Helper\Abstract_Helper
{
    /**
     * Returns array of quoted orders with direction
     *
     * @param \Magento\Framework\DB\Select $select
     * @param bool $autoReset
     * @return array
     */
    protected function _prepare_order(\Magento\Framework\DB\Select $select, $auto_reset = false)
    {
        $select_orders = $select->get_part(\Magento\Framework\DB\Select::ORDER);
        if (!$select_orders) {
            return [];
        }
        $orders = [];
        foreach ($select_orders as $term) {
            if (is_array($term)) {
                if (!is_numeric($term[0])) {
                    $orders[] = sprintf('%s %s', $this->get_connection()->quote_identifier($term[0], true), $term[1]);
                }
            } else if (!is_numeric($term)) {
                $orders[] = $this->get_connection()->quote_identifier($term, true);
            }
        }
        if ($auto_reset) {
            $select->reset(\Magento\Framework\DB\Select::ORDER);
        }
        return $orders;
    }
    /**
     * Truncate alias name from field.
     *
     * Result string depends from second optional argument $reverse
     * which can be true if you need the first part of the field.
     * Field can be with 'dot' delimiter.
     *
     * @param string $field
     * @param bool $reverse OPTIONAL
     * @return string
     */
    protected function _truncate_alias_name($field, $reverse = false)
    {
        $string = $field;
        if ($field !== null && !is_numeric($field) && strpos($field, '.') !== false) {
            $size = strpos($field, '.');
            if ($reverse) {
                $string = substr($field, 0, $size);
            } else {
                $string = substr($field, $size + 1);
            }
        }
        return $string;
    }
    /**
     * Returns quoted group by fields
     *
     * @param \Magento\Framework\DB\Select $select
     * @param bool $autoReset
     * @return array
     */
    protected function _prepare_group(\Magento\Framework\DB\Select $select, $auto_reset = false)
    {
        $select_groups = $select->get_part(\Magento\Framework\DB\Select::GROUP);
        if (!$select_groups) {
            return [];
        }
        $groups = [];
        foreach ($select_groups as $term) {
            $groups[] = $this->get_connection()->quote_identifier($term, true);
        }
        if ($auto_reset) {
            $select->reset(\Magento\Framework\DB\Select::GROUP);
        }
        return $groups;
    }
    /**
     * Prepare and returns having array
     *
     * @param \Magento\Framework\DB\Select $select
     * @param bool $autoReset
     * @return array
     * @throws \Zend_Db_Exception
     */
    protected function _prepare_having(\Magento\Framework\DB\Select $select, $auto_reset = false)
    {
        $select_havings = $select->get_part(\Magento\Framework\DB\Select::HAVING);
        if (!$select_havings) {
            return [];
        }
        $havings = [];
        $columns = $select->get_part(\Magento\Framework\DB\Select::COLUMNS);
        foreach ($columns as $column_entry) {
            $correlation_name = (string) $column_entry[1];
            $column = $column_entry[2];
            foreach ($select_havings as $having) {
                /**
                 * Looking for column expression in the having clause
                 */
                if ($having !== null && strpos($having, $correlation_name) !== false) {
                    if (is_string($column)) {
                        /**
                         * Replace column expression to column alias in having clause
                         */
                        $havings[] = str_replace($correlation_name, $column, $having);
                    } else {
                        throw new \Zend_Db_Exception(sprintf("Can't prepare expression without column alias: '%s'", $correlation_name));
                    }
                }
            }
        }
        if ($auto_reset) {
            $select->reset(\Magento\Framework\DB\Select::HAVING);
        }
        return $havings;
    }
    /**
     * Assemble limit
     *
     * @param string $query
     * @param int $limitCount
     * @param int $limitOffset
     * @param array $columnList
     * @return string
     */
    protected function _assemble_limit($query, $limit_count, $limit_offset, $column_list = [])
    {
        if ($limit_count !== null) {
            $limit_count = (int) $limit_count;
            $limit_offset = (int) $limit_offset;
            if ($limit_offset + $limit_count != $limit_offset + 1) {
                $columns = [];
                foreach ($column_list as $column_entry) {
                    $columns[] = $column_entry[2] ? $column_entry[2] : $column_entry[1];
                }
                $query = sprintf('%s LIMIT %s, %s', $query, $limit_count, $limit_offset);
            }
        }
        return $query;
    }
    /**
     * Prepare select column list
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string|null $groupByCondition OPTIONAL
     * @return mixed|array
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepare_columns_list(\Magento\Framework\DB\Select $select, $group_by_condition = null)
    {
        if (!count($select->get_part(\Magento\Framework\DB\Select::FROM))) {
            return $select->get_part(\Magento\Framework\DB\Select::COLUMNS);
        }
        $columns = $select->get_part(\Magento\Framework\DB\Select::COLUMNS);
        $tables = $select->get_part(\Magento\Framework\DB\Select::FROM);
        $prepared_columns = [];
        foreach ($columns as $column_entry) {
            list($correlation_name, $column, $alias) = $column_entry;
            if ($column instanceof \Zend_Db_Expr) {
                if ($alias !== null) {
                    if (preg_match('/(^|[^a-zA-Z_])^(SELECT)?(SUM|MIN|MAX|AVG|COUNT)\s*\(/i', $column)) {
                        $column = new \Zend_Db_Expr($column);
                    }
                    $prepared_columns[strtoupper($alias)] = [null, $column, $alias];
                } else {
                    throw new \Zend_Db_Exception("Can't prepare expression without alias");
                }
            } else if ($column == \Magento\Framework\DB\Select::SQL_WILDCARD) {
                if ($tables[$correlation_name]['tableName'] instanceof \Zend_Db_Expr) {
                    throw new \Zend_Db_Exception("Can't prepare expression when tableName is instance of \\Zend_Db_Expr");
                }
                $table_columns = $this->get_connection()->describe_table($tables[$correlation_name]['tableName']);
                foreach (array_keys($table_columns) as $col) {
                    $prepared_columns[strtoupper($col)] = [$correlation_name, $col, null];
                }
            } else {
                $column_key = $alias === null ? $column : $alias;
                $prepared_columns[strtoupper($column_key)] = [$correlation_name, $column, $alias];
            }
        }
        return $prepared_columns;
    }
    /**
     * Add prepared column group_concat expression
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $fieldAlias Field alias which will be added with column group_concat expression
     * @param string $fields
     * @param string $groupConcatDelimiter
     * @param string $fieldsDelimiter
     * @param string $additionalWhere
     * @return \Magento\Framework\DB\Select
     */
    public function add_group_concat_column($select, $field_alias, $fields, $group_concat_delimiter = ',', $fields_delimiter = '', $additional_where = '')
    {
        if (is_array($fields)) {
            $field_expr = $this->get_connection()->get_concat_sql($fields, $fields_delimiter);
        } else {
            $field_expr = $fields;
        }
        if ($additional_where) {
            $field_expr = $this->get_connection()->get_check_sql($additional_where, $field_expr, "''");
        }
        $separator = '';
        if ($group_concat_delimiter) {
            $separator = sprintf(" SEPARATOR '%s'", $group_concat_delimiter);
        }
        $select->columns([$field_alias => new \Zend_Db_Expr(sprintf('GROUP_CONCAT(%s%s)', $field_expr, $separator))]);
        return $select;
    }
    /**
     * Returns expression of days passed from $startDate to $endDate
     *
     * @param  string|\Zend_Db_Expr $startDate
     * @param  string|\Zend_Db_Expr $endDate
     * @return \Zend_Db_Expr
     */
    public function get_date_diff($start_date, $end_date)
    {
        $date_diff = "TIMESTAMPDIFF(DAY, {$start_date}, {$end_date})";
        return new \Zend_Db_Expr($date_diff);
    }
    /**
     * Escapes and quotes LIKE value.
     * Stating escape symbol in expression is not required, because we use standard MySQL escape symbol.
     * For options and escaping see escapeLikeValue().
     *
     * @param string $value
     * @param array $options
     * @return \Zend_Db_Expr
     *
     * @see escapeLikeValue()
     */
    public function add_like_escape($value, $options = [])
    {
        $value = $this->escape_like_value($value, $options);
        return new \Zend_Db_Expr($this->get_connection()->quote($value));
    }
}