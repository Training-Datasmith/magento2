<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\TypeResolver;

/**
 * Create operation for list of bulk operations.
 */
class Create implements \Magento\Framework\EntityManager\Operation\CreateInterface
{
    public function __construct(private readonly MetadataPool $metadataPool, private readonly TypeResolver $typeResolver, private readonly ResourceConnection $resourceConnection)
    {
    }

    /**
     * Save all operations from the list in one query.
     *
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $this->resourceConnection->getConnection($metadata->getEntityConnectionName());
        try {
            $connection->beginTransaction();
            $data = [];
            foreach ($entity->getItems() as $operation) {
                $data[] = $operation->getData();
            }
            $connection->insertOnDuplicate(
                $metadata->getEntityTable(),
                $data,
                [
                    'status',
                    'error_code',
                    'result_message',
                ]
            );
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $entity;
    }
}
