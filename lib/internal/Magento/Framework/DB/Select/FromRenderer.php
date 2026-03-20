<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Platform\Quote;
use Magento\Framework\DB\Select;
class From_Renderer implements Renderer_Interface
{
    /**
     * @var Quote
     */
    protected $quote;
    /**
     * @param Quote $quote
     */
    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }
    /**
     * Render FROM & JOIN's section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     * @throws \Zend_Db_Select_Exception
     */
    public function render(Select $select, $sql = '')
    {
        /*
         * If no table specified, use RDBMS-dependent solution
         * for table-less query.  e.g. DUAL in Oracle.
         */
        $source = $select->get_part(Select::FROM);
        if (empty($source)) {
            $source = [];
        }
        $from = [];
        foreach ($source as $correlation_name => $table) {
            $tmp = '';
            $join_type = $table['joinType'] == Select::FROM ? Select::INNER_JOIN : $table['joinType'];
            // Add join clause (if applicable)
            if (!empty($from)) {
                $tmp .= ' ' . strtoupper($join_type) . ' ';
            }
            $tmp .= $this->get_quoted_schema($table['schema']);
            $tmp .= $this->get_quoted_table($table['tableName'], $correlation_name);
            // Add join conditions (if applicable)
            if (!empty($from) && !empty($table['joinCondition'])) {
                $tmp .= ' ' . Select::SQL_ON . ' ' . $table['joinCondition'];
            }
            // Add the table name and condition add to the list
            $from[] = $tmp;
        }
        // Add the list of all joins
        if (!empty($from)) {
            $sql .= ' ' . Select::SQL_FROM . ' ' . implode("\n", $from);
        }
        return $sql;
    }
    /**
     * Return a quoted schema name
     *
     * @param string   $schema  The schema name OPTIONAL
     * @return string|null
     */
    protected function get_quoted_schema($schema = null)
    {
        if ($schema === null) {
            return null;
        }
        return $this->quote->quote_identifier($schema) . '.';
    }
    /**
     * Return a quoted table name
     *
     * @param string   $tableName        The table name
     * @param string   $correlationName  The correlation name OPTIONAL
     * @return string
     */
    protected function get_quoted_table($table_name, $correlation_name = null)
    {
        return $this->quote->quote_table_as($table_name, $correlation_name);
    }
}