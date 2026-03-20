<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Report_Xml\DB\Assembler;

use Magento\Analytics\Report_Xml\DB\Select_Builder;
/**
 * Interface AssemblerInterface
 *
 * Introduces family of SQL assemblers
 * Each assembler populates SelectBuilder with config information
 * @see usage examples at \Magento\Analytics\ReportXml\QueryFactory
 *
 * @api
 */
interface Assembler_Interface
{
    /**
     * Assemble SQL statement
     *
     * @param array $queryConfig
     * @return SelectBuilder
     */
    public function assemble(Select_Builder $select_builder, $query_config);
}