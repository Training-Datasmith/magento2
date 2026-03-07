<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Bulk\OperationManagementInterface;
use Psr\Log\LoggerInterface;

/**
 * Class for managing Bulk Operations
 */
class OperationManagement implements OperationManagementInterface
{
    /**
     * OperationManagement constructor.
     */
    public function __construct(OperationInterfaceFactory $operationFactory, private readonly LoggerInterface $logger, private readonly ResourceConnection $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function changeOperationStatus(
        $bulkUuid,
        $operationKey,
        $status,
        $errorCode = null,
        $message = null,
        $data = null,
        $resultData = null
    ): bool {
        try {
            $connection = $this->connection->getConnection();
            $table = $this->connection->getTableName('magento_operation');
            $bind = [
                'error_code' => $errorCode,
                'status' => $status,
                'result_message' => $message,
                'serialized_data' => $data,
                'result_serialized_data' => $resultData,
            ];
            $where = ['bulk_uuid = ?' => $bulkUuid, 'operation_key = ?' => $operationKey];
            $connection->update($table, $bind, $where);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return false;
        }
        return true;
    }
}
