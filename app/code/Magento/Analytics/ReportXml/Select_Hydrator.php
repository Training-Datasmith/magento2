<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Select;
use Magento\Framework\Object_Manager_Interface;
/**
 * Hydrator for report select parts
 */
class Select_Hydrator
{
    /**
     * Array of supported Select parts
     */
    private array $predefined_select_parts = [Select::DISTINCT, Select::COLUMNS, Select::UNION, Select::FROM, Select::WHERE, Select::GROUP, Select::HAVING, Select::ORDER, Select::LIMIT_COUNT, Select::LIMIT_OFFSET, Select::FOR_UPDATE];
    /**
     * @param array $selectParts
     */
    public function __construct(private readonly Resource_Connection $resource_connection, private readonly Object_Manager_Interface $object_manager, private $select_parts = [])
    {
    }
    /**
     * Perform merge of parts
     */
    private function get_select_parts(): array
    {
        return array_merge($this->predefined_select_parts, $this->select_parts);
    }
    /**
     * Extracts Select metadata parts
     *
     * @throws \Zend_Db_Select_Exception
     */
    public function extract(Select $select): array
    {
        $parts = [];
        foreach ($this->get_select_parts() as $part_name) {
            $parts[$part_name] = $select->get_part($part_name);
        }
        return $parts;
    }
    /**
     * Set parts to the select object
     *
     * @return Select
     */
    public function recreate(array $select_parts)
    {
        $select = $this->resource_connection->get_connection()->select();
        $select = $this->process_columns($select, $select_parts);
        foreach ($select_parts as $part_name => $part_value) {
            $select->set_part($part_name, $part_value);
        }
        return $select;
    }
    /**
     * Process COLUMNS part values and add this part into select.
     *
     * If each column contains information about select expression
     * an object with the type of this expression going to be created and assigned to this column.
     */
    private function process_columns(Select $select, array &$select_parts): Select
    {
        if (!empty($select_parts[Select::COLUMNS]) && is_array($select_parts[Select::COLUMNS])) {
            $part = [];
            foreach ($select_parts[Select::COLUMNS] as $column_entry) {
                [$correlation_name, $column, $alias] = $column_entry;
                if (!empty($column['class'])) {
                    $expression = $this->object_manager->create($column['class'], $column['arguments'] ?? []);
                    $part[] = [$correlation_name, $expression, $alias];
                } else {
                    $part[] = $column_entry;
                }
            }
            $select->set_part(Select::COLUMNS, $part);
            unset($select_parts[Select::COLUMNS]);
        }
        return $select;
    }
}