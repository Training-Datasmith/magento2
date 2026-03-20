<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface_Factory;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Bulk\Operation_Management_Interface;
use Psr\Log\Logger_Interface;
/**
 * Class for managing Bulk Operations
 */
class Operation_Management implements Operation_Management_Interface
{
    /**
     * OperationManagement constructor.
     */
    public function __construct(Operation_Interface_Factory $operation_factory, private readonly Logger_Interface $logger, private readonly Resource_Connection $connection)
    {
    }
    /**
     * @inheritDoc
     */
    public function change_operation_status($bulk_uuid, $operation_key, $status, $error_code = null, $message = null, $data = null, $result_data = null): bool
    {
        try {
            $connection = $this->connection->get_connection();
            $table = $this->connection->get_table_name('magento_operation');
            $bind = ['error_code' => $error_code, 'status' => $status, 'result_message' => $message, 'serialized_data' => $data, 'result_serialized_data' => $result_data];
            $where = ['bulk_uuid = ?' => $bulk_uuid, 'operation_key = ?' => $operation_key];
            $connection->update($table, $bind, $where);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->get_message());
            return false;
        }
        return true;
    }
}