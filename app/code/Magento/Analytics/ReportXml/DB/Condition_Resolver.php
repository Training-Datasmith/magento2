<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\DB;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Sql\Expression;
/**
 * Mapper for WHERE conditions
 */
class Condition_Resolver
{
    private array $condition_map = ['eq' => '%1$s = %2$s', 'neq' => '%1$s != %2$s', 'like' => '%1$s LIKE %2$s', 'nlike' => '%1$s NOT LIKE %2$s', 'in' => '%1$s IN(%2$s)', 'nin' => '%1$s NOT IN(%2$s)', 'notnull' => '%1$s IS NOT NULL', 'null' => '%1$s IS NULL', 'gt' => '%1$s > %2$s', 'lt' => '%1$s < %2$s', 'gteq' => '%1$s >= %2$s', 'lteq' => '%1$s <= %2$s', 'finset' => 'FIND_IN_SET(%2$s, %1$s)'];
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;
    /**
     * ConditionResolver constructor.
     */
    public function __construct(private readonly Resource_Connection $resource_connection)
    {
    }
    /**
     * Returns connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function get_connection()
    {
        if (!$this->connection) {
            $this->connection = $this->resource_connection->get_connection();
        }
        return $this->connection;
    }
    /**
     * Returns value for condition
     *
     * @param string $condition
     * @param string $referencedEntity
     * @return mixed|null|string|\Zend_Db_Expr
     */
    private function get_value(array $condition, ?string $referenced_entity)
    {
        $value = null;
        $argument = $condition['_value'] ?? null;
        if (!isset($condition['type'])) {
            $condition['type'] = 'value';
        }
        return match ($condition['type']) {
            'value' => $this->get_connection()->quote($argument),
            'variable' => new Expression($argument),
            'identifier' => $this->get_connection()->quote_identifier($referenced_entity ? $referenced_entity . '.' . $argument : $argument),
            default => $value,
        };
    }
    /**
     * Returns condition for WHERE
     *
     * @param null|string $referencedEntity
     */
    private function get_condition(Select_Builder $select_builder, string $table_name, array $condition, $referenced_entity = null): string
    {
        $columns = $select_builder->get_columns();
        if (isset($columns[$condition['attribute']]) && $columns[$condition['attribute']] instanceof Expression) {
            $expression = $columns[$condition['attribute']];
        } else {
            $expression = $this->get_connection()->quote_identifier($table_name . '.' . $condition['attribute']);
        }
        return sprintf($this->condition_map[$condition['operator']], $expression, $this->get_value($condition, $referenced_entity));
    }
    /**
     * Build WHERE condition
     *
     * @param array $filterConfig
     * @param string $aliasName
     * @param null|string $referencedAlias
     * @return array
     */
    public function get_filter(Select_Builder $select_builder, $filter_config, $alias_name, $referenced_alias = null): string
    {
        $filters_parts = [];
        foreach ($filter_config as $filter) {
            $glue = $filter['glue'];
            $parts = [];
            foreach ($filter['condition'] as $condition) {
                if (isset($condition['type']) && $condition['type'] == 'variable') {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $select_builder->set_params(array_merge($select_builder->get_params(), [$condition['_value']]));
                }
                $parts[] = $this->get_condition($select_builder, $alias_name, $condition, $referenced_alias);
            }
            if (isset($filter['filter'])) {
                $parts[] = '(' . $this->get_filter($select_builder, $filter['filter'], $alias_name, $referenced_alias) . ')';
            }
            $filters_parts[] = '(' . implode(' ' . strtoupper((string) $glue) . ' ', $parts) . ')';
        }
        return implode(' OR ', $filters_parts);
    }
}