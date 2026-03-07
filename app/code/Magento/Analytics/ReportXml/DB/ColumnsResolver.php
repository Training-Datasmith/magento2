<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\ColumnValueExpression;

/**
 * Resolves columns names
 */
class ColumnsResolver
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * ColumnsResolver constructor.
     */
    public function __construct(private readonly NameResolver $nameResolver, private readonly ResourceConnection $resourceConnection)
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
     * Set columns list to SelectBuilder
     *
     * @return array
     */
    public function getColumns(SelectBuilder $selectBuilder, array $entityConfig)
    {
        if (!isset($entityConfig['attribute'])) {
            return [];
        }
        $group = [];
        $columns = $selectBuilder->getColumns();
        foreach ($entityConfig['attribute'] as $attributeData) {
            $columnAlias = $this->nameResolver->getAlias($attributeData);
            $tableAlias = $this->nameResolver->getAlias($entityConfig);
            $columnName = $this->nameResolver->getName($attributeData);
            if (isset($attributeData['function'])) {
                $prefix = '';
                if (!empty($attributeData['distinct'])) {
                    $prefix = ' DISTINCT ';
                }
                $expression = new ColumnValueExpression(
                    strtoupper($attributeData['function']) . '(' . $prefix
                    . $this->getConnection()->quoteIdentifier($tableAlias . '.' . $columnName)
                    . ')'
                );
            } else {
                $expression = $tableAlias . '.' . $columnName;
            }
            $columns[$columnAlias] = $expression;
            if (isset($attributeData['group'])) {
                $group[$columnAlias] = $expression;
            }
        }
        $selectBuilder->setGroup(array_merge($selectBuilder->getGroup(), $group));
        return $columns;
    }
}
