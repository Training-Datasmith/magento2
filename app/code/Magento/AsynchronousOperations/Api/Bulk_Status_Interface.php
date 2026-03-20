<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Api;

/**
 * Interface BulkStatusInterface.
 *
 * Bulk summary data with list of operations items short data.
 *
 * @api
 * @since 100.2.3
 */
interface Bulk_Status_Interface extends \Magento\Framework\Bulk\Bulk_Status_Interface
{
    /**
     * Get Bulk summary data with list of operations items full data.
     *
     * @param string $bulkUuid
     * @return \Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 100.2.3
     */
    public function get_bulk_detailed_status($bulk_uuid);
    /**
     * Get Bulk summary data with list of operations items short data.
     *
     * @param string $bulkUuid
     * @return \Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 100.2.3
     */
    public function get_bulk_short_status($bulk_uuid);
}