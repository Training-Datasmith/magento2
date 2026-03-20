<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\DB;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Sql\Column_Value_Expression;
/**
 * Resolves columns names
 */
class Columns_Resolver
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;
    /**
     * ColumnsResolver constructor.
     */
    public function __construct(private readonly Name_Resolver $name_resolver, private readonly Resource_Connection $resource_connection)
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
     * Set columns list to SelectBuilder
     *
     * @return array
     */
    public function get_columns(Select_Builder $select_builder, array $entity_config)
    {
        if (!isset($entity_config['attribute'])) {
            return [];
        }
        $group = [];
        $columns = $select_builder->get_columns();
        foreach ($entity_config['attribute'] as $attribute_data) {
            $column_alias = $this->name_resolver->get_alias($attribute_data);
            $table_alias = $this->name_resolver->get_alias($entity_config);
            $column_name = $this->name_resolver->get_name($attribute_data);
            if (isset($attribute_data['function'])) {
                $prefix = '';
                if (!empty($attribute_data['distinct'])) {
                    $prefix = ' DISTINCT ';
                }
                $expression = new Column_Value_Expression(strtoupper($attribute_data['function']) . '(' . $prefix . $this->get_connection()->quote_identifier($table_alias . '.' . $column_name) . ')');
            } else {
                $expression = $table_alias . '.' . $column_name;
            }
            $columns[$column_alias] = $expression;
            if (isset($attribute_data['group'])) {
                $group[$column_alias] = $expression;
            }
        }
        $select_builder->set_group(array_merge($select_builder->get_group(), $group));
        return $columns;
    }
}