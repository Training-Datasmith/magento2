<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\DB;

/**
 * Interface QueryInterface
 *
 * @api
 */
interface Query_Interface
{
    /**
     * Retrieve source Criteria object
     *
     * @return \Magento\Framework\Api\CriteriaInterface
     */
    public function get_criteria();
    /**
     * Retrieve all ids for query
     *
     * @return array
     */
    public function get_all_ids();
    /**
     * Add variable to bind list
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function add_bind_param($name, $value);
    /**
     * Get collection size
     *
     * @return int
     */
    public function get_size();
    /**
     * Get sql select string or object
     *
     * @param bool $stringMode
     * @return string || Select
     */
    public function get_select_sql($string_mode = false);
    /**
     * Reset Statement object
     *
     * @return void
     */
    public function reset();
    /**
     * Fetch all statement
     *
     * @return array
     */
    public function fetch_all();
    /**
     * Fetch statement
     *
     * @return mixed
     */
    public function fetch_item();
    /**
     * Get Identity Field Name
     *
     * @return string
     */
    public function get_id_field_name();
    /**
     * Retrieve connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function get_connection();
    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function get_resource();
    /**
     * Add Select Part to skip from count query
     *
     * @param string $name
     * @param bool $toSkip
     * @return void
     */
    public function add_count_sql_skip_part($name, $to_skip = true);
}