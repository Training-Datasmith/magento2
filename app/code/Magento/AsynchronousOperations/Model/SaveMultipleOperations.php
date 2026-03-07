<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\SaveMultipleOperationsInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation as OperationResource;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Implementation for saving multiple operations
 */
class SaveMultipleOperations implements SaveMultipleOperationsInterface
{
    /**
     * BulkSummary constructor.
     */
    public function __construct(private readonly OperationResource $operationResource)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(array $operations): void
    {
        try {
            $operationsToInsert = array_map(fn (\Magento\AsynchronousOperations\Api\Data\OperationInterface $operation) => $operation->getData(), $operations);

            $connection = $this->operationResource->getConnection();
            $connection->insertMultiple(
                $this->operationResource->getTable(OperationResource::TABLE_NAME),
                $operationsToInsert
            );
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }
}
