<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation\Create;

use Magento\Framework\Entity_Manager\Db\Create_Row;
use Magento\Framework\Entity_Manager\Hydrator_Pool;
use Magento\Framework\Entity_Manager\Type_Resolver;
/**
 * Class CreateMain
 */
class Create_Main
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
     * @var CreateRow
     */
    private $create_row;
    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param CreateRow $createRow
     */
    public function __construct(Type_Resolver $type_resolver, Hydrator_Pool $hydrator_pool, Create_Row $create_row)
    {
        $this->type_resolver = $type_resolver;
        $this->hydrator_pool = $hydrator_pool;
        $this->create_row = $create_row;
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $hydrator = $this->hydrator_pool->get_hydrator($entity_type);
        $arguments = array_merge($hydrator->extract($entity), $arguments);
        $entity_data = $this->create_row->execute($entity_type, $arguments);
        $entity = $hydrator->hydrate($entity, $entity_data);
        return $entity;
    }
}