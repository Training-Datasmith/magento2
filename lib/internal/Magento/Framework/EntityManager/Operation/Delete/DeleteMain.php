<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation\Delete;

use Magento\Framework\Entity_Manager\Db\Delete_Row;
use Magento\Framework\Entity_Manager\Hydrator_Pool;
use Magento\Framework\Entity_Manager\Type_Resolver;
/**
 * Class DeleteMain
 */
class Delete_Main
{
    /**
     * @var TypeResolver
     */
    private $type_resolver;
    /**
     * @var HydratorPool
     */
    private $hydrator_pool;
    /**
     * @var DeleteRow
     */
    private $delete_row;
    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param DeleteRow $deleteRow
     */
    public function __construct(Type_Resolver $type_resolver, Hydrator_Pool $hydrator_pool, Delete_Row $delete_row)
    {
        $this->type_resolver = $type_resolver;
        $this->hydrator_pool = $hydrator_pool;
        $this->delete_row = $delete_row;
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $hydrator = $this->hydrator_pool->get_hydrator($entity_type);
        $arguments = array_merge($hydrator->extract($entity), $arguments);
        $this->delete_row->execute($entity_type, $arguments);
        return $entity;
    }
}