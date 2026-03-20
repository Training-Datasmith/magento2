<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface BulkStatusInterface
 * @api
 * @since 103.0.0
 */
interface Bulk_Status_Interface
{
    /**
     * Get failed operations by bulk uuid
     *
     * @param string $bulkUuid
     * @param int|null $failureType
     * @return \Magento\Framework\Bulk\OperationInterface[]
     * @since 103.0.0
     */
    public function get_failed_operations_by_bulk_id($bulk_uuid, $failure_type = null);
    /**
     * Get operations count by bulk uuid and status.
     *
     * @param string $bulkUuid
     * @param int $status
     * @return int
     * @since 103.0.0
     */
    public function get_operations_count_by_bulk_id_and_status($bulk_uuid, $status);
    /**
     * Get all bulks created by user
     *
     * @param int $userId
     * @return BulkSummaryInterface[]
     * @since 103.0.0
     */
    public function get_bulks_by_user($user_id);
    /**
     * Computational status based on statuses of belonging operations
     *
     * FINISHED_SUCCESFULLY - all operations are handled succesfully
     * FINISHED_WITH_FAILURE - some operations are handled with failure
     *
     * @param string $bulkUuid
     * @return int NOT_STARTED | IN_PROGRESS | FINISHED_SUCCESFULLY | FINISHED_WITH_FAILURE
     * @since 103.0.0
     */
    public function get_bulk_status($bulk_uuid);
}