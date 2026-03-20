<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\DB;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Select;
/**
 * Responsible for Select object creation, works as a builder. Returns Select as result;
 *
 * Used in SQL assemblers.
 *
 * @api
 */
class Select_Builder
{
    /**
     * @var string
     */
    private $connection_name;
    /**
     * @var array
     */
    private $from;
    /**
     * @var array
     */
    private $group = [];
    /**
     * @var array
     */
    private $columns = [];
    /**
     * @var array
     */
    private $filters = [];
    /**
     * @var array
     */
    private $joins = [];
    /**
     * @var array
     */
    private $params = [];
    /**
     * @var array
     */
    private $having = [];
    /**
     * SelectBuilder constructor.
     */
    public function __construct(private readonly Resource_Connection $resource_connection)
    {
    }
    /**
     * Get join condition
     *
     * @return array
     */
    public function get_joins()
    {
        return $this->joins;
    }
    /**
     * Set joins conditions
     *
     * @param array $joins
     * @return $this
     */
    public function set_joins($joins): static
    {
        $this->joins = $joins;
        return $this;
    }
    /**
     * Get connection name
     *
     * @return string
     */
    public function get_connection_name()
    {
        return $this->connection_name;
    }
    /**
     * Set connection name
     *
     * @param string $connectionName
     * @return $this
     */
    public function set_connection_name($connection_name): static
    {
        $this->connection_name = $connection_name;
        return $this;
    }
    /**
     * Get columns
     *
     * @return array
     */
    public function get_columns()
    {
        return $this->columns;
    }
    /**
     * Set columns
     *
     * @param array $columns
     * @return $this
     */
    public function set_columns($columns): static
    {
        $this->columns = $columns;
        return $this;
    }
    /**
     * Get filters
     *
     * @return array
     */
    public function get_filters()
    {
        return $this->filters;
    }
    /**
     * Set filters
     *
     * @param array $filters
     * @return $this
     */
    public function set_filters($filters): static
    {
        $this->filters = $filters;
        return $this;
    }
    /**
     * Get from condition
     *
     * @return array
     */
    public function get_from()
    {
        return $this->from;
    }
    /**
     * Set from condition
     *
     * @param array $from
     * @return $this
     */
    public function set_from($from): static
    {
        $this->from = $from;
        return $this;
    }
    /**
     * Process JOIN conditions
     */
    private function process_join(Select $select, array $join_config): Select
    {
        match ($join_config['link-type']) {
            'left' => $select->join_left($join_config['table'], $join_config['condition'], []),
            'inner' => $select->join_inner($join_config['table'], $join_config['condition'], []),
            'right' => $select->join_right($join_config['table'], $join_config['condition'], []),
            default => $select,
        };
        return $select;
    }
    /**
     * Creates Select object
     *
     * @return Select
     */
    public function create()
    {
        $connection = $this->resource_connection->get_connection($this->get_connection_name());
        $select = $connection->select();
        $select->from($this->get_from(), []);
        $select->columns($this->get_columns());
        foreach ($this->get_filters() as $filter) {
            $select->where($filter);
        }
        foreach ($this->get_joins() as $join_config) {
            $select = $this->process_join($select, $join_config);
        }
        if (!empty($this->get_group())) {
            $select->group(implode(', ', $this->get_group()));
        }
        return $select;
    }
    /**
     * Returns group
     *
     * @return array
     */
    public function get_group()
    {
        return $this->group;
    }
    /**
     * Set group
     *
     * @param array $group
     * @return $this
     */
    public function set_group($group): static
    {
        $this->group = $group;
        return $this;
    }
    /**
     * Get parameters
     *
     * @return array
     */
    public function get_params()
    {
        return $this->params;
    }
    /**
     * Set parameters
     *
     * @param array $params
     * @return $this
     */
    public function set_params($params): static
    {
        $this->params = $params;
        return $this;
    }
    /**
     * Get having condition
     *
     * @return array
     */
    public function get_having()
    {
        return $this->having;
    }
    /**
     * Set having condition
     *
     * @param array $having
     * @return $this
     */
    public function set_having($having): static
    {
        $this->having = $having;
        return $this;
    }
}