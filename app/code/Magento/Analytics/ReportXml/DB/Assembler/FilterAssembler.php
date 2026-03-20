<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\DB\Assembler;

use Magento\Analytics\Report_Xml\DB\Condition_Resolver;
use Magento\Analytics\Report_Xml\DB\Name_Resolver;
use Magento\Analytics\Report_Xml\DB\Select_Builder;
/**
 * Assembles WHERE conditions
 */
class Filter_Assembler implements Assembler_Interface
{
    /**
     * FilterAssembler constructor.
     */
    public function __construct(private readonly Condition_Resolver $condition_resolver, private readonly Name_Resolver $name_resolver)
    {
    }
    /**
     * Assembles WHERE conditions
     *
     * @param array $queryConfig
     */
    public function assemble(Select_Builder $select_builder, $query_config): Select_Builder
    {
        if (!isset($query_config['source']['filter'])) {
            return $select_builder;
        }
        $filters = $this->condition_resolver->get_filter($select_builder, $query_config['source']['filter'], $this->name_resolver->get_alias($query_config['source']));
        $select_builder->set_filters(array_merge_recursive($select_builder->get_filters(), [$filters]));
        return $select_builder;
    }
}