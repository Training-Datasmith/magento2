<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\DB\Helper\Mysql;

use Magento\Framework\App\Resource_Connection;
/**
 * MySQL Fulltext Query Builder
 */
class Fulltext
{
    /**
     * Characters that have special meaning in fulltext match syntax
     *
     * @var string
     */
    public const SPECIAL_CHARACTERS = '-+<>*()~?';
    /**
     * FULLTEXT search in MySQL search mode "natural language"
     */
    public const FULLTEXT_MODE_NATURAL = 'IN NATURAL LANGUAGE MODE';
    /**
     * FULLTEXT search in MySQL search mode "natural language with query expansion"
     */
    public const FULLTEXT_MODE_NATURAL_QUERY = 'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION';
    /**
     * FULLTEXT search in MySQL search mode "boolean"
     */
    public const FULLTEXT_MODE_BOOLEAN = 'IN BOOLEAN MODE';
    /**
     * FULLTEXT search in MySQL search mode "query expansion"
     */
    public const FULLTEXT_MODE_QUERY = 'WITH QUERY EXPANSION';
    /**
     * FULLTEXT search in MySQL MATCH method
     */
    public const MATCH = 'MATCH';
    /**
     * FULLTEXT search in MySQL AGAINST method
     */
    public const AGAINST = 'AGAINST';
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;
    /**
     * @param ResourceConnection $resource
     */
    public function __construct(Resource_Connection $resource)
    {
        $this->connection = $resource->get_connection();
    }
    /**
     * Method for FULLTEXT search in Mysql, will generated MATCH ($columns) AGAINST ('$expression' $mode)
     *
     * @param string|string[] $columns Columns which add to MATCH ()
     * @param string $expression Expression which add to AGAINST ()
     * @param string $mode
     * @return string
     */
    public function get_match_query($columns, $expression, $mode = self::FULLTEXT_MODE_NATURAL)
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }
        $expression = $this->connection->quote($expression);
        $condition = self::MATCH . " ({$columns}) " . self::AGAINST . " ({$expression} {$mode})";
        return $condition;
    }
    /**
     * Method for FULLTEXT search in Mysql; will add generated MATCH ($columns) AGAINST ('$expression' $mode) to $select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string|string[] $columns Columns which add to MATCH ()
     * @param string $expression Expression which add to AGAINST ()
     * @param bool $isCondition true=AND, false=OR
     * @param string $mode
     * @return \Magento\Framework\DB\Select
     */
    public function match($select, $columns, $expression, $is_condition = true, $mode = self::FULLTEXT_MODE_NATURAL)
    {
        $full_condition = $this->get_match_query($columns, $expression, $mode);
        if ($is_condition) {
            $select->where($full_condition);
        } else {
            $select->or_where($full_condition);
        }
        return $select;
    }
    /**
     * Remove special characters from fulltext query expression
     *
     * @param string $expression
     * @return string
     */
    public function remove_special_characters(string $expression): string
    {
        return str_replace(str_split(static::SPECIAL_CHARACTERS), '', $expression);
    }
}