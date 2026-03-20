<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\DB\Assembler;

use Magento\Analytics\Report_Xml\DB\Columns_Resolver;
use Magento\Analytics\Report_Xml\DB\Condition_Resolver;
use Magento\Analytics\Report_Xml\DB\Name_Resolver;
use Magento\Analytics\Report_Xml\DB\Select_Builder;
use Magento\Framework\App\Resource_Connection;
/**
 * Assembles JOIN conditions
 */
class Join_Assembler implements Assembler_Interface
{
    public function __construct(private readonly Condition_Resolver $condition_resolver, private readonly Columns_Resolver $columns_resolver, private readonly Name_Resolver $name_resolver, private readonly Resource_Connection $resource_connection)
    {
    }
    /**
     * Assembles JOIN conditions
     *
     * @param array $queryConfig
     */
    public function assemble(Select_Builder $select_builder, $query_config): Select_Builder
    {
        if (!isset($query_config['source']['link-source'])) {
            return $select_builder;
        }
        $joins = [];
        $filters = $select_builder->get_filters();
        $source_alias = $this->name_resolver->get_alias($query_config['source']);
        foreach ($query_config['source']['link-source'] as $join) {
            $join_alias = $this->name_resolver->get_alias($join);
            $joins[$join_alias] = ['link-type' => $join['link-type'] ?? 'left', 'table' => [$join_alias => $this->resource_connection->get_table_name($this->name_resolver->get_name($join))], 'condition' => $this->condition_resolver->get_filter($select_builder, $join['using'], $join_alias, $source_alias)];
            if (isset($join['filter'])) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $filters = array_merge($filters, [$this->condition_resolver->get_filter($select_builder, $join['filter'], $join_alias, $source_alias)]);
            }
            $columns = $this->columns_resolver->get_columns($select_builder, isset($join['attribute']) ? $join : []);
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $select_builder->set_columns(array_merge($select_builder->get_columns(), $columns));
        }
        $select_builder->set_filters($filters);
        $select_builder->set_joins(array_merge($select_builder->get_joins(), $joins));
        return $select_builder;
    }
}