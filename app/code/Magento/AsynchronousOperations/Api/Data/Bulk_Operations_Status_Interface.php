<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Api\Data;

/**
 * Interface BulkStatusInterface
 *
 * Bulk summary data with list of operations items summary data.
 *
 * @api
 * @since 100.2.3
 */
interface Bulk_Operations_Status_Interface extends Bulk_Summary_Interface
{
    public const OPERATIONS_LIST = 'operations_list';
    /**
     * Retrieve list of operation with statuses (short data).
     *
     * @return \Magento\AsynchronousOperations\Api\Data\SummaryOperationStatusInterface[]
     * @since 100.2.3
     */
    public function get_operations_list();
    /**
     * Set operations list.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\SummaryOperationStatusInterface[] $operationStatusList
     * @return $this
     * @since 100.2.3
     */
    public function set_operations_list($operation_status_list);
}