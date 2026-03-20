<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\DB\Assembler;

use Magento\Analytics\Report_Xml\DB\Columns_Resolver;
use Magento\Analytics\Report_Xml\DB\Name_Resolver;
use Magento\Analytics\Report_Xml\DB\Select_Builder;
use Magento\Framework\App\Resource_Connection;
/**
 * Assembles FROM condition
 */
class From_Assembler implements Assembler_Interface
{
    public function __construct(private readonly Name_Resolver $name_resolver, private readonly Columns_Resolver $columns_resolver, private readonly Resource_Connection $resource_connection)
    {
    }
    /**
     * Assembles FROM condition
     *
     * @param array $queryConfig
     */
    public function assemble(Select_Builder $select_builder, $query_config): Select_Builder
    {
        $select_builder->set_from([$this->name_resolver->get_alias($query_config['source']) => $this->resource_connection->get_table_name($this->name_resolver->get_name($query_config['source']))]);
        $columns = $this->columns_resolver->get_columns($select_builder, $query_config['source']);
        $select_builder->set_columns(array_merge($select_builder->get_columns(), $columns));
        return $select_builder;
    }
}