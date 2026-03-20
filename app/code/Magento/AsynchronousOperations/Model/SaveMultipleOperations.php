<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Save_Multiple_Operations_Interface;
use Magento\Asynchronous_Operations\Model\Resource_Model\Operation as OperationResource;
use Magento\Framework\Exception\Could_Not_Save_Exception;
/**
 * Implementation for saving multiple operations
 */
class Save_Multiple_Operations implements Save_Multiple_Operations_Interface
{
    /**
     * BulkSummary constructor.
     */
    public function __construct(private readonly Operation_Resource $operation_resource)
    {
    }
    /**
     * @inheritDoc
     */
    public function execute(array $operations): void
    {
        try {
            $operations_to_insert = array_map(fn(\Magento\Asynchronous_Operations\Api\Data\Operation_Interface $operation) => $operation->get_data(), $operations);
            $connection = $this->operation_resource->get_connection();
            $connection->insert_multiple($this->operation_resource->get_table(Operation_Resource::TABLE_NAME), $operations_to_insert);
        } catch (\Exception $exception) {
            throw new Could_Not_Save_Exception(__($exception->get_message()));
        }
    }
}