<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Platform\Quote;
use Magento\Framework\DB\Select;
/**
 * Class ColumnsRenderer
 */
class Columns_Renderer implements Renderer_Interface
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
     * Render COLUMNS section
     *
     * @param Select $select
     * @param string $sql
     * @return null|string
     * @throws \Zend_Db_Select_Exception
     */
    public function render(Select $select, $sql = '')
    {
        if (!count($select->get_part(Select::COLUMNS))) {
            return null;
        }
        $columns = [];
        foreach ($select->get_part(Select::COLUMNS) as $column_entry) {
            list($correlation_name, $column, $alias) = $column_entry;
            if ($column instanceof \Zend_Db_Expr) {
                $columns[] = $this->quote->quote_column_as($column, $alias);
            } else {
                if ($column == Select::SQL_WILDCARD) {
                    $column = new \Zend_Db_Expr(Select::SQL_WILDCARD);
                    $alias = null;
                }
                if (empty($correlation_name)) {
                    $columns[] = $this->quote->quote_column_as($column, $alias);
                } else {
                    $columns[] = $this->quote->quote_column_as([$correlation_name, $column], $alias);
                }
            }
        }
        return $sql . ' ' . implode(', ', $columns);
    }
}