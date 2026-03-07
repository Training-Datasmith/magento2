<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\ColumnsResolver;
use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\App\ResourceConnection;

/**
 * Assembles FROM condition
 */
class FromAssembler implements AssemblerInterface
{
    public function __construct(private readonly NameResolver $nameResolver, private readonly ColumnsResolver $columnsResolver, private readonly ResourceConnection $resourceConnection)
    {
    }

    /**
     * Assembles FROM condition
     *
     * @param array $queryConfig
     */
    public function assemble(SelectBuilder $selectBuilder, $queryConfig): SelectBuilder
    {
        $selectBuilder->setFrom(
            [
                $this->nameResolver->getAlias($queryConfig['source']) =>
                    $this->resourceConnection
                        ->getTableName($this->nameResolver->getName($queryConfig['source'])),
            ]
        );
        $columns = $this->columnsResolver->getColumns($selectBuilder, $queryConfig['source']);
        $selectBuilder->setColumns(array_merge($selectBuilder->getColumns(), $columns));
        return $selectBuilder;
    }
}
