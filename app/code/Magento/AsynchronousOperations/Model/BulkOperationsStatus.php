<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\BulkStatusInterface;
use Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterface;
use Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterfaceFactory as BulkStatusShortFactory;
use Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterface;
use Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterfaceFactory as BulkStatusDetailedFactory;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory as OperationCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Process bulk operations status.
 */
class BulkOperationsStatus implements BulkStatusInterface
{
    public function __construct(private readonly BulkStatus $bulkStatus, private readonly OperationCollectionFactory $operationCollectionFactory, private readonly BulkStatusDetailedFactory $bulkDetailedFactory, private readonly BulkStatusShortFactory $bulkShortFactory, private readonly EntityManager $entityManager)
    {
    }

    /**
     * @inheritDoc
     */
    public function getFailedOperationsByBulkId($bulkUuid, $failureType = null)
    {
        return $this->bulkStatus->getFailedOperationsByBulkId($bulkUuid, $failureType);
    }

    /**
     * @inheritDoc
     */
    public function getOperationsCountByBulkIdAndStatus($bulkUuid, $status)
    {
        return $this->operationCollectionFactory->create()
            ->addFieldToFilter('bulk_uuid', $bulkUuid)
            ->addFieldToFilter('status', $status)
            ->getSize();
    }

    /**
     * @inheritDoc
     */
    public function getBulksByUser($userId)
    {
        return $this->bulkStatus->getBulksByUser($userId);
    }

    /**
     * @inheritDoc
     */
    public function getBulkStatus($bulkUuid)
    {
        return $this->bulkStatus->getBulkStatus($bulkUuid);
    }

    /**
     * @inheritDoc
     */
    public function getBulkDetailedStatus($bulkUuid)
    {
        $bulkSummary = $this->bulkDetailedFactory->create();

        /** @var DetailedBulkOperationsStatusInterface $bulk */
        $bulk = $this->entityManager->load($bulkSummary, $bulkUuid);

        if ($bulk->getBulkId() === null) {
            throw new NoSuchEntityException(
                __(
                    'Bulk uuid %bulkUuid not exist',
                    ['bulkUuid' => $bulkUuid]
                )
            );
        }
        $operations = $this->operationCollectionFactory->create()
            ->addFieldToFilter('bulk_uuid', $bulkUuid)
            ->getItems();
        $bulk->setOperationsList($operations);

        return $bulk;
    }

    /**
     * @inheritDoc
     */
    public function getBulkShortStatus($bulkUuid)
    {
        $bulkSummary = $this->bulkShortFactory->create();

        /** @var BulkOperationsStatusInterface $bulk */
        $bulk = $this->entityManager->load($bulkSummary, $bulkUuid);
        if ($bulk->getBulkId() === null) {
            throw new NoSuchEntityException(
                __(
                    'Bulk uuid %bulkUuid not exist',
                    ['bulkUuid' => $bulkUuid]
                )
            );
        }
        $operations = $this->operationCollectionFactory->create()
            ->addFieldToFilter('bulk_uuid', $bulkUuid)
            ->getItems();
        $bulk->setOperationsList($operations);

        return $bulk;
    }
}
