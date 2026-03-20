<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface BulkManagementInterface
 * @api
 * @since 103.0.0
 */
interface Bulk_Management_Interface
{
    /**
     * Schedule new bulk
     *
     * @param string $bulkUuid
     * @param OperationInterface[] $operations
     * @param string $description
     * @param int $userId
     * @return boolean
     * @since 103.0.0
     */
    public function schedule_bulk($bulk_uuid, array $operations, $description, $user_id = null);
    /**
     * Delete bulk
     *
     * @param string $bulkId
     * @return boolean
     * @since 103.0.0
     */
    public function delete_bulk($bulk_id);
}