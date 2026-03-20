<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface OperationManagementInterface
 * @api
 * @since 103.0.0
 */
interface Operation_Management_Interface
{
    /**
     * Used by consumer to change status after processing operation
     *
     * @param string $bulkUuid
     * @param int $operationKey
     * @param int $status
     * @param int|null $errorCode
     * @param string|null $message property to update Result Message
     * @param string|null $data serialized data object of failed message
     * @return boolean
     * @since 103.0.0
     */
    public function change_operation_status($bulk_uuid, $operation_key, $status, $error_code = null, $message = null, $data = null);
    // @codingStandardsIgnoreLine
}