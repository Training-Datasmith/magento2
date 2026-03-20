<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Platform;

use Magento\Framework\DB\Select;
class Quote
{
    /**
     * Return quoted identifier
     *
     * @param string $identifier
     * @return string
     */
    public function quote_identifier($identifier)
    {
        return $this->quote_identifier_as($identifier);
    }
    /**
     * Return quoted column with alias
     *
     * @param string $identifier
     * @param string|null $alias
     * @return string
     */
    public function quote_column_as($identifier, $alias = null)
    {
        return $this->quote_identifier_as($identifier, $alias);
    }
    /**
     * Return quoted table with alias
     *
     * @param string $identifier
     * @param string|null $alias
     * @return string
     */
    public function quote_table_as($identifier, $alias = null)
    {
        return $this->quote_identifier_as($identifier, $alias);
    }
    /**
     * Return quoted identifier with alias
     *
     * @param string $identifier
     * @param string|null $alias
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function quote_identifier_as($identifier, $alias = null)
    {
        if ($identifier instanceof \Zend_Db_Expr) {
            $quoted = $identifier->__toString();
        } elseif ($identifier instanceof \Magento\Framework\DB\Select) {
            $quoted = '(' . $identifier->assemble() . ')';
        } else {
            if (is_string($identifier)) {
                $identifier = explode('.', $identifier);
            }
            if (is_array($identifier)) {
                $segments = [];
                foreach ($identifier as $segment) {
                    if ($segment instanceof \Zend_Db_Expr) {
                        $segments[] = $segment->__toString();
                    } else {
                        $segments[] = $this->replace_quote_symbol($segment);
                    }
                }
                if ($alias !== null && end($identifier) == $alias) {
                    $alias = null;
                }
                $quoted = implode('.', $segments);
            } else {
                $quoted = $this->replace_quote_symbol($identifier);
            }
        }
        if ($alias !== null) {
            $quoted .= ' ' . Select::SQL_AS . ' ' . $this->replace_quote_symbol($alias);
        }
        return $quoted;
    }
    /**
     * Replace quote symbol
     *
     * @param string $value
     * @return string
     */
    protected function replace_quote_symbol($value)
    {
        $symbol = $this->get_quote_identifier_symbol();
        return $symbol . str_replace("{$symbol}", "{$symbol}{$symbol}", (string) $value) . $symbol;
    }
    /**
     * Get quote identifier symbol
     *
     * @return string
     */
    protected function get_quote_identifier_symbol()
    {
        return '`';
    }
}