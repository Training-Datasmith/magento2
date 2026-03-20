<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Resource_Model\Operation;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Entity_Manager\Type_Resolver;
/**
 * Create operation for list of bulk operations.
 */
class Create implements \Magento\Framework\Entity_Manager\Operation\Create_Interface
{
    public function __construct(private readonly Metadata_Pool $metadata_pool, private readonly Type_Resolver $type_resolver, private readonly Resource_Connection $resource_connection)
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
        $entity_type = $this->type_resolver->resolve($entity);
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        $connection = $this->resource_connection->get_connection($metadata->get_entity_connection_name());
        try {
            $connection->begin_transaction();
            $data = [];
            foreach ($entity->get_items() as $operation) {
                $data[] = $operation->get_data();
            }
            $connection->insert_on_duplicate($metadata->get_entity_table(), $data, ['status', 'error_code', 'result_message']);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->roll_back();
            throw $e;
        }
        return $entity;
    }
}