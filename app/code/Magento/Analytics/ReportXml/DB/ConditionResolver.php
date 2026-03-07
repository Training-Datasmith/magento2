<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;

/**
 * Mapper for WHERE conditions
 */
class ConditionResolver
{
    private array $conditionMap = [
        'eq' => '%1$s = %2$s',
        'neq' => '%1$s != %2$s',
        'like' => '%1$s LIKE %2$s',
        'nlike' => '%1$s NOT LIKE %2$s',
        'in' => '%1$s IN(%2$s)',
        'nin' => '%1$s NOT IN(%2$s)',
        'notnull' => '%1$s IS NOT NULL',
        'null' => '%1$s IS NULL',
        'gt' => '%1$s > %2$s',
        'lt' => '%1$s < %2$s',
        'gteq' => '%1$s >= %2$s',
        'lteq' => '%1$s <= %2$s',
        'finset' => 'FIND_IN_SET(%2$s, %1$s)',
    ];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * ConditionResolver constructor.
     */
    public function __construct(private readonly ResourceConnection $resourceConnection)
    {
    }

    /**
     * Returns connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
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
    private function getValue(array $condition, ?string $referencedEntity)
    {
        $value = null;
        $argument = $condition['_value'] ?? null;
        if (!isset($condition['type'])) {
            $condition['type'] = 'value';
        }
        return match ($condition['type']) {
            'value' => $this->getConnection()->quote($argument),
            'variable' => new Expression($argument),
            'identifier' => $this->getConnection()->quoteIdentifier(
                $referencedEntity ? $referencedEntity . '.' . $argument : $argument
            ),
            default => $value,
        };
    }

    /**
     * Returns condition for WHERE
     *
     * @param null|string $referencedEntity
     */
    private function getCondition(SelectBuilder $selectBuilder, string $tableName, array $condition, $referencedEntity = null): string
    {
        $columns = $selectBuilder->getColumns();
        if (isset($columns[$condition['attribute']])
            && $columns[$condition['attribute']] instanceof Expression
        ) {
            $expression = $columns[$condition['attribute']];
        } else {
            $expression = $this->getConnection()->quoteIdentifier($tableName . '.' . $condition['attribute']);
        }
        return sprintf(
            $this->conditionMap[$condition['operator']],
            $expression,
            $this->getValue($condition, $referencedEntity)
        );
    }

    /**
     * Build WHERE condition
     *
     * @param array $filterConfig
     * @param string $aliasName
     * @param null|string $referencedAlias
     * @return array
     */
    public function getFilter(SelectBuilder $selectBuilder, $filterConfig, $aliasName, $referencedAlias = null): string
    {
        $filtersParts = [];
        foreach ($filterConfig as $filter) {
            $glue = $filter['glue'];
            $parts = [];
            foreach ($filter['condition'] as $condition) {
                if (isset($condition['type']) && $condition['type'] == 'variable') {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $selectBuilder->setParams(array_merge($selectBuilder->getParams(), [$condition['_value']]));
                }
                $parts[] = $this->getCondition(
                    $selectBuilder,
                    $aliasName,
                    $condition,
                    $referencedAlias
                );
            }
            if (isset($filter['filter'])) {
                $parts[] = '(' . $this->getFilter(
                    $selectBuilder,
                    $filter['filter'],
                    $aliasName,
                    $referencedAlias
                ) . ')';
            }
            $filtersParts[] = '(' . implode(' ' . strtoupper((string) $glue) . ' ', $parts) . ')';
        }
        return implode(' OR ', $filtersParts);
    }
}
