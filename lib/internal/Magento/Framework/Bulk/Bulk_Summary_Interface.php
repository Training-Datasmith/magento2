<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface BulkSummaryInterface
 * @api
 * @since 103.0.0
 */
interface Bulk_Summary_Interface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const BULK_ID = 'uuid';
    public const DESCRIPTION = 'description';
    public const START_TIME = 'start_time';
    public const USER_ID = 'user_id';
    public const OPERATION_COUNT = 'operation_count';
    /**#@-*/
    /**#@+
     * Bulk statuses constants
     */
    public const NOT_STARTED = 0;
    public const IN_PROGRESS = 1;
    public const FINISHED_SUCCESSFULLY = 2;
    public const FINISHED_WITH_FAILURE = 3;
    /**#@-*/
    /**
     * Get bulk uuid
     *
     * @return string
     * @since 103.0.0
     */
    public function get_bulk_id();
    /**
     * Set bulk uuid
     *
     * @param string $bulkUuid
     * @return $this
     * @since 103.0.0
     */
    public function set_bulk_id($bulk_uuid);
    /**
     * Get bulk description
     *
     * @return string
     * @since 103.0.0
     */
    public function get_description();
    /**
     * Set bulk description
     *
     * @param string $description
     * @return $this
     * @since 103.0.0
     */
    public function set_description($description);
    /**
     * Get bulk scheduled time
     *
     * @return string
     * @since 103.0.0
     */
    public function get_start_time();
    /**
     * Set bulk scheduled time
     *
     * @param string $timestamp
     * @return $this
     * @since 103.0.0
     */
    public function set_start_time($timestamp);
    /**
     * Get user id
     *
     * @return int
     * @since 103.0.0
     */
    public function get_user_id();
    /**
     * Set user id
     *
     * @param int $userId
     * @return $this
     * @since 103.0.0
     */
    public function set_user_id($user_id);
    /**
     * Get total number of operations scheduled in scope of this bulk
     *
     * @return int
     * @since 103.0.0
     */
    public function get_operation_count();
    /**
     * Set total number of operations scheduled in scope of this bulk
     *
     * @param int $operationCount
     * @return $this
     * @since 103.0.0
     */
    public function set_operation_count($operation_count);
}