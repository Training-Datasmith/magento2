<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Psr\Log\Logger_Interface as Logger;
/**
 * Class Query
 */
class Query implements Query_Interface
{
    /**
     * Select object
     *
     * @var \Magento\Framework\DB\Select
     */
    protected $select;
    /**
     * @var \Magento\Framework\Api\CriteriaInterface
     */
    protected $criteria;
    /**
     * Resource instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $resource;
    /**
     * Database's statement for fetch item one by one
     *
     * @var \Zend_Db_Statement_Pdo
     */
    protected $fetch_stmt = null;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     */
    private $fetch_strategy;
    /**
     * @var array
     */
    protected $bind_params = [];
    /**
     * @var int
     */
    protected $total_records;
    /**
     * @var mixed
     */
    protected $data;
    /**
     * Query Select Parts to be skipped when prepare query for count
     *
     * @var array
     */
    protected $count_sql_skip_parts = [\Magento\Framework\DB\Select::ORDER => true, \Magento\Framework\DB\Select::LIMIT_COUNT => true, \Magento\Framework\DB\Select::LIMIT_OFFSET => true, \Magento\Framework\DB\Select::COLUMNS => true];
    /**
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Framework\Api\CriteriaInterface $criteria
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     */
    public function __construct(\Magento\Framework\DB\Select $select, \Magento\Framework\Api\Criteria_Interface $criteria, \Magento\Framework\Model\Resource_Model\Db\Abstract_Db $resource, \Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface $fetch_strategy)
    {
        $this->select = $select;
        $this->criteria = $criteria;
        $this->resource = $resource;
        $this->fetch_strategy = $fetch_strategy;
    }
    /**
     * Retrieve source Criteria object
     *
     * @return \Magento\Framework\Api\CriteriaInterface
     */
    public function get_criteria()
    {
        return $this->criteria;
    }
    /**
     * Retrieve all ids for query
     *
     * @return array
     */
    public function get_all_ids()
    {
        $ids_select = clone $this->get_select();
        $ids_select->reset(\Magento\Framework\DB\Select::ORDER);
        $ids_select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $ids_select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $ids_select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $ids_select->columns($this->get_resource()->get_id_field_name(), 'main_table');
        return $this->get_connection()->fetch_col($ids_select, $this->bind_params);
    }
    /**
     * Add variable to bind list
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function add_bind_param($name, $value)
    {
        $this->bind_params[$name] = $value;
    }
    /**
     * Get collection size
     *
     * @return int
     */
    public function get_size()
    {
        if ($this->total_records === null) {
            $sql = $this->get_select_count_sql();
            $this->total_records = $this->get_connection()->fetch_one($sql, $this->bind_params);
        }
        return (int) $this->total_records;
    }
    /**
     * Get sql select string or object
     *
     * @param bool $stringMode
     * @return string || Select
     */
    public function get_select_sql($string_mode = false)
    {
        if ($string_mode) {
            return $this->select->__toString();
        }
        return $this->select;
    }
    /**
     * Reset Statement object
     *
     * @return void
     */
    public function reset()
    {
        $this->fetch_stmt = null;
        $this->data = null;
    }
    /**
     * Fetch all statement
     *
     * @return array
     */
    public function fetch_all()
    {
        if ($this->data === null) {
            $select = $this->get_select();
            $this->data = $this->fetch_strategy->fetch_all($select, $this->bind_params);
        }
        return $this->data;
    }
    /**
     * Fetch statement
     *
     * @return mixed
     */
    public function fetch_item()
    {
        if (null === $this->fetch_stmt) {
            $this->fetch_stmt = $this->get_connection()->query($this->get_select(), $this->bind_params);
        }
        $data = $this->fetch_stmt->fetch();
        if (!$data) {
            $data = [];
        }
        return $data;
    }
    /**
     * Get Identity Field Name
     *
     * @return string
     */
    public function get_id_field_name()
    {
        return $this->get_resource()->get_id_field_name();
    }
    /**
     * Retrieve connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function get_connection()
    {
        return $this->get_select()->get_connection();
    }
    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function get_resource()
    {
        return $this->resource;
    }
    /**
     * Add Select Part to skip from count query
     *
     * @param string $name
     * @param bool $toSkip
     * @return void
     */
    public function add_count_sql_skip_part($name, $to_skip = true)
    {
        $this->count_sql_skip_parts[$name] = $to_skip;
    }
    /**
     * Get SQL for get record count
     *
     * @return Select
     */
    protected function get_select_count_sql()
    {
        $count_select = clone $this->get_select();
        foreach ($this->get_count_sql_skip_parts() as $part => $to_skip) {
            if ($to_skip) {
                $count_select->reset($part);
            }
        }
        $count_select->columns('COUNT(*)');
        return $count_select;
    }
    /**
     * Returned count SQL skip parts
     *
     * @return array
     */
    protected function get_count_sql_skip_parts()
    {
        return $this->count_sql_skip_parts;
    }
    /**
     * Get \Magento\Framework\DB\Select object instance
     *
     * @return Select
     */
    protected function get_select()
    {
        return $this->select;
    }
}