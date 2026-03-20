<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Entity_Manager\Hydrator_Pool;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Entity_Manager\Type_Resolver;
/**
 * Class CheckIfExists
 */
class Check_If_Exists implements Check_If_Exists_Interface
{
    /**
     * @var ResourceConnection
     */
    private $resource_connection;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var HydratorPool
     */
    private $hydrator_pool;
    /**
     * @var TypeResolver
     */
    private $type_resolver;
    /**
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(Type_Resolver $type_resolver, Metadata_Pool $metadata_pool, Hydrator_Pool $hydrator_pool, Resource_Connection $resource_connection)
    {
        $this->metadata_pool = $metadata_pool;
        $this->hydrator_pool = $hydrator_pool;
        $this->type_resolver = $type_resolver;
        $this->resource_connection = $resource_connection;
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $metadata = $this->metadata_pool->get_metadata($entity_type);
        $hydrator = $this->hydrator_pool->get_hydrator($entity_type);
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        $entity_data = $hydrator->extract($entity);
        if (!isset($entity_data[$metadata->get_identifier_field()])) {
            return false;
        }
        return (bool) $connection->fetch_one($connection->select()->from($metadata->get_entity_table(), [$metadata->get_identifier_field()])->where($metadata->get_identifier_field() . ' = ?', $entity_data[$metadata->get_identifier_field()])->limit(1));
    }
}