<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Exception;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\MessageQueue\BulkPublisherInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Asynchronous Bulk Management
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkManagement implements BulkManagementInterface
{
    /**
     * @var BulkSummaryInterfaceFactory
     */
    private $bulkSummaryFactory;

    /**
     * @var CollectionFactory
     */
    private $operationCollectionFactory;

    /**
     * BulkManagement constructor.
     */
    public function __construct(
        private readonly EntityManager $entityManager,
        BulkSummaryInterfaceFactory $bulkSummaryFactory,
        CollectionFactory $operationCollectionFactory,
        private readonly BulkPublisherInterface $publisher,
        private readonly MetadataPool $metadataPool,
        private readonly ResourceConnection $resourceConnection,
        private readonly LoggerInterface $logger,
        private readonly UserContextInterface $userContext
    ) {
        $this->bulkSummaryFactory = $bulkSummaryFactory;
        $this->operationCollectionFactory = $operationCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function scheduleBulk($bulkUuid, array $operations, $description, $userId = null): bool
    {
        $userType = $this->userContext->getUserType();
        if ($userType === null) {
            $userType = UserContextInterface::USER_TYPE_ADMIN;
        }
        if ($userId === null && $userType === UserContextInterface::USER_TYPE_ADMIN) {
            $userId = $this->userContext->getUserId();
        }

        $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        // save bulk summary and related operations
        $connection->beginTransaction();
        try {
            /** @var BulkSummaryInterface $bulkSummary */
            $bulkSummary = $this->bulkSummaryFactory->create();
            $this->entityManager->load($bulkSummary, $bulkUuid);
            $bulkSummary->setBulkId($bulkUuid);
            $bulkSummary->setDescription($description);
            $bulkSummary->setUserId($userId);
            $bulkSummary->setUserType($userType);
            $bulkSummary->setOperationCount((int)$bulkSummary->getOperationCount() + count($operations));
            $this->entityManager->save($bulkSummary);

            $this->publishOperations($operations);

            $connection->commit();
        } catch (Exception $exception) {
            $connection->rollBack();
            $this->logger->critical($exception->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Retry bulk operations that failed due to given errors.
     *
     * @param string $bulkUuid target bulk UUID
     * @param array $errorCodes list of corresponding error codes
     * @return int number of affected bulk operations
     */
    public function retryBulk($bulkUuid, array $errorCodes): int
    {
        /** @var Collection $collection */
        $collection = $this->operationCollectionFactory->create();
        /** @var Operation[] $retriablyFailedOperations */
        $retriablyFailedOperations = $collection
            ->addFieldToFilter(OperationInterface::BULK_ID, ['eq' => $bulkUuid])
            ->addFieldToFilter(OperationInterface::ERROR_CODE, ['in' => $errorCodes])
            ->getItems();
        $affectedOperations = count($retriablyFailedOperations);
        if ($retriablyFailedOperations) {
            $operation = reset($retriablyFailedOperations);
            //async consumer expects operations to be in the database
            // thus such operation should not be deleted but reopened
            $shouldReopen = str_starts_with($operation->getTopicName() ?? '', ConfigInterface::TOPIC_PREFIX);
            $metadata = $this->metadataPool->getMetadata(OperationInterface::class);
            $linkField = $metadata->getLinkField();
            $ids = [];
            foreach ($retriablyFailedOperations as $operation) {
                $ids[] = (int) $operation->getData($linkField);
            }
            $batchSize = 10000;
            $chunks = array_chunk($ids, $batchSize);
            $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
            $connection->beginTransaction();
            try {
                if ($shouldReopen) {
                    foreach ($chunks as $chunk) {
                        $connection->update(
                            $metadata->getEntityTable(),
                            [
                                OperationInterface::STATUS => OperationInterface::STATUS_TYPE_OPEN,
                                OperationInterface::RESULT_SERIALIZED_DATA => null,
                                OperationInterface::ERROR_CODE => null,
                                OperationInterface::RESULT_MESSAGE => null,
                                'started_at' => null,
                            ],
                            [
                                $linkField . ' IN (?)' => $chunk,
                            ]
                        );
                    }
                } else {
                    foreach ($chunks as $chunk) {
                        $connection->delete(
                            $metadata->getEntityTable(),
                            [
                                $linkField . ' IN (?)' => $chunk,
                            ]
                        );
                    }
                }
                $connection->commit();
            } catch (Throwable $exception) {
                $connection->rollBack();
                $this->logger->critical($exception->getMessage());
                $affectedOperations = 0;
            }

            if ($affectedOperations) {
                $this->publishOperations($retriablyFailedOperations);
            }
        }

        return $affectedOperations;
    }

    /**
     * Publish list of operations to the corresponding message queues.
     */
    private function publishOperations(array $operations): void
    {
        $operationsByTopics = [];
        foreach ($operations as $operation) {
            $operationsByTopics[$operation->getTopicName()][] = $operation;
        }
        foreach ($operationsByTopics as $topicName => $operations) {
            $this->publisher->publish($topicName, $operations);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteBulk($bulkId)
    {
        return $this->entityManager->delete(
            $this->entityManager->load(
                $this->bulkSummaryFactory->create(),
                $bulkId
            )
        );
    }
}
