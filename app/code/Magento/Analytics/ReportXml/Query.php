<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml;

use Magento\Framework\DB\Select;
/**
 * Query object, contains SQL statement, information about connection, query arguments
 */
class Query implements \JsonSerializable
{
    private ?object $select_count = null;
    /**
     * Query constructor.
     *
     * @param string $connectionName
     * @param array $config
     */
    public function __construct(private readonly Select $select, private readonly Select_Hydrator $select_hydrator, private $connection_name, private $config)
    {
    }
    /**
     * Returns query select
     *
     * @return Select
     */
    public function get_select()
    {
        return $this->select;
    }
    /**
     * Returns Connection name
     *
     * @return string
     */
    public function get_connection_name()
    {
        return $this->connection_name;
    }
    /**
     * Returns configuration
     *
     * @return array
     */
    public function get_config()
    {
        return $this->config;
    }
    /**
     * @inheritDoc
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return ['connectionName' => $this->get_connection_name(), 'select_parts' => $this->select_hydrator->extract($this->get_select()), 'config' => $this->get_config()];
    }
    /**
     * Get SQL for get record count
     *
     * @throws \Zend_Db_Select_Exception
     */
    public function get_select_count_sql(): Select
    {
        if (!$this->select_count) {
            $this->select_count = clone $this->get_select();
            $this->select_count->reset(\Magento\Framework\DB\Select::ORDER);
            $this->select_count->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
            $this->select_count->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
            $this->select_count->reset(\Magento\Framework\DB\Select::COLUMNS);
            $part = $this->get_select()->get_part(\Magento\Framework\DB\Select::GROUP);
            if (!is_array($part) || !count($part)) {
                $this->select_count->columns(new \Zend_Db_Expr('COUNT(*)'));
                return $this->select_count;
            }
            $this->select_count->reset(\Magento\Framework\DB\Select::GROUP);
            $group = $this->get_select()->get_part(\Magento\Framework\DB\Select::GROUP);
            $this->select_count->columns(new \Zend_Db_Expr('COUNT(DISTINCT ' . implode(', ', $group) . ')'));
        }
        return $this->select_count;
    }
}