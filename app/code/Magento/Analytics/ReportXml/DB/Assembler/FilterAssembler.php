<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\ConditionResolver;
use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;

/**
 * Assembles WHERE conditions
 */
class FilterAssembler implements AssemblerInterface
{
    /**
     * FilterAssembler constructor.
     */
    public function __construct(private readonly ConditionResolver $conditionResolver, private readonly NameResolver $nameResolver)
    {
    }

    /**
     * Assembles WHERE conditions
     *
     * @param array $queryConfig
     */
    public function assemble(SelectBuilder $selectBuilder, $queryConfig): SelectBuilder
    {
        if (!isset($queryConfig['source']['filter'])) {
            return $selectBuilder;
        }
        $filters = $this->conditionResolver->getFilter(
            $selectBuilder,
            $queryConfig['source']['filter'],
            $this->nameResolver->getAlias($queryConfig['source'])
        );
        $selectBuilder->setFilters(array_merge_recursive($selectBuilder->getFilters(), [$filters]));
        return $selectBuilder;
    }
}
