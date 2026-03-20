<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation\Read;

use Magento\Framework\Entity_Manager\Db\Read_Row;
use Magento\Framework\Entity_Manager\Hydrator_Pool;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Entity_Manager\Type_Resolver;
class Read_Main
{
    /**
     * @var TypeResolver
     */
    private $type_resolver;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var HydratorPool
     */
    private $hydrator_pool;
    /**
     * @var ReadRow
     */
    private $read_row;
    /**
     * @param TypeResolver $typeResolver
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param ReadRow $readRow
     */
    public function __construct(Type_Resolver $type_resolver, Metadata_Pool $metadata_pool, Hydrator_Pool $hydrator_pool, Read_Row $read_row)
    {
        $this->type_resolver = $type_resolver;
        $this->metadata_pool = $metadata_pool;
        $this->hydrator_pool = $hydrator_pool;
        $this->read_row = $read_row;
    }
    /**
     * @param object $entity
     * @param string $identifier
     * @return object
     */
    public function execute($entity, $identifier)
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $hydrator = $this->hydrator_pool->get_hydrator($entity_type);
        $entity_data = $this->read_row->execute($entity_type, $identifier);
        $entity = $hydrator->hydrate($entity, $entity_data);
        return $entity;
    }
}