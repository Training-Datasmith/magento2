<?php

declare(strict_types=1);
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
interface BulkSummaryInterface
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
    public function getBulkId();

    /**
     * Set bulk uuid
     *
     * @param string $bulkUuid
     * @return $this
     * @since 103.0.0
     */
    public function setBulkId($bulkUuid);

    /**
     * Get bulk description
     *
     * @return string
     * @since 103.0.0
     */
    public function getDescription();

    /**
     * Set bulk description
     *
     * @param string $description
     * @return $this
     * @since 103.0.0
     */
    public function setDescription($description);

    /**
     * Get bulk scheduled time
     *
     * @return string
     * @since 103.0.0
     */
    public function getStartTime();

    /**
     * Set bulk scheduled time
     *
     * @param string $timestamp
     * @return $this
     * @since 103.0.0
     */
    public function setStartTime($timestamp);

    /**
     * Get user id
     *
     * @return int
     * @since 103.0.0
     */
    public function getUserId();

    /**
     * Set user id
     *
     * @param int $userId
     * @return $this
     * @since 103.0.0
     */
    public function setUserId($userId);

    /**
     * Get total number of operations scheduled in scope of this bulk
     *
     * @return int
     * @since 103.0.0
     */
    public function getOperationCount();

    /**
     * Set total number of operations scheduled in scope of this bulk
     *
     * @param int $operationCount
     * @return $this
     * @since 103.0.0
     */
    public function setOperationCount($operationCount);
}
