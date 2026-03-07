<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\ColumnsResolver;
use Magento\Analytics\ReportXml\DB\ConditionResolver;
use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\App\ResourceConnection;

/**
 * Assembles JOIN conditions
 */
class JoinAssembler implements AssemblerInterface
{
    public function __construct(private readonly ConditionResolver $conditionResolver, private readonly ColumnsResolver $columnsResolver, private readonly NameResolver $nameResolver, private readonly ResourceConnection $resourceConnection)
    {
    }

    /**
     * Assembles JOIN conditions
     *
     * @param array $queryConfig
     */
    public function assemble(SelectBuilder $selectBuilder, $queryConfig): SelectBuilder
    {
        if (!isset($queryConfig['source']['link-source'])) {
            return $selectBuilder;
        }
        $joins = [];
        $filters = $selectBuilder->getFilters();

        $sourceAlias = $this->nameResolver->getAlias($queryConfig['source']);

        foreach ($queryConfig['source']['link-source'] as $join) {
            $joinAlias = $this->nameResolver->getAlias($join);

            $joins[$joinAlias]  = [
                'link-type' => $join['link-type'] ?? 'left',
                'table' => [
                    $joinAlias => $this->resourceConnection
                        ->getTableName($this->nameResolver->getName($join)),
                ],
                'condition' => $this->conditionResolver->getFilter(
                    $selectBuilder,
                    $join['using'],
                    $joinAlias,
                    $sourceAlias
                ),
            ];
            if (isset($join['filter'])) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $filters = array_merge(
                    $filters,
                    [
                        $this->conditionResolver->getFilter(
                            $selectBuilder,
                            $join['filter'],
                            $joinAlias,
                            $sourceAlias
                        ),
                    ]
                );
            }
            $columns = $this->columnsResolver->getColumns($selectBuilder, isset($join['attribute']) ? $join : []);
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $selectBuilder->setColumns(array_merge($selectBuilder->getColumns(), $columns));
        }
        $selectBuilder->setFilters($filters);
        $selectBuilder->setJoins(array_merge($selectBuilder->getJoins(), $joins));
        return $selectBuilder;
    }
}
