<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Responsible for Select object creation, works as a builder. Returns Select as result;
 *
 * Used in SQL assemblers.
 *
 * @api
 */
class SelectBuilder
{
    /**
     * @var string
     */
    private $connectionName;

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
    public function __construct(private readonly ResourceConnection $resourceConnection)
    {
    }

    /**
     * Get join condition
     *
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * Set joins conditions
     *
     * @param array $joins
     * @return $this
     */
    public function setJoins($joins): static
    {
        $this->joins = $joins;

        return $this;
    }

    /**
     * Get connection name
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Set connection name
     *
     * @param string $connectionName
     * @return $this
     */
    public function setConnectionName($connectionName): static
    {
        $this->connectionName = $connectionName;

        return $this;
    }

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set columns
     *
     * @param array $columns
     * @return $this
     */
    public function setColumns($columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set filters
     *
     * @param array $filters
     * @return $this
     */
    public function setFilters($filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get from condition
     *
     * @return array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set from condition
     *
     * @param array $from
     * @return $this
     */
    public function setFrom($from): static
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Process JOIN conditions
     */
    private function processJoin(Select $select, array $joinConfig): Select
    {
        match ($joinConfig['link-type']) {
            'left' => $select->joinLeft($joinConfig['table'], $joinConfig['condition'], []),
            'inner' => $select->joinInner($joinConfig['table'], $joinConfig['condition'], []),
            'right' => $select->joinRight($joinConfig['table'], $joinConfig['condition'], []),
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
        $connection = $this->resourceConnection->getConnection($this->getConnectionName());
        $select = $connection->select();
        $select->from($this->getFrom(), []);
        $select->columns($this->getColumns());
        foreach ($this->getFilters() as $filter) {
            $select->where($filter);
        }
        foreach ($this->getJoins() as $joinConfig) {
            $select = $this->processJoin($select, $joinConfig);
        }
        if (!empty($this->getGroup())) {
            $select->group(implode(', ', $this->getGroup()));
        }
        return $select;
    }

    /**
     * Returns group
     *
     * @return array
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set group
     *
     * @param array $group
     * @return $this
     */
    public function setGroup($group): static
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set parameters
     *
     * @param array $params
     * @return $this
     */
    public function setParams($params): static
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get having condition
     *
     * @return array
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * Set having condition
     *
     * @param array $having
     * @return $this
     */
    public function setHaving($having): static
    {
        $this->having = $having;

        return $this;
    }
}
