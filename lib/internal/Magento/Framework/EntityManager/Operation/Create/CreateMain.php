<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\EntityManager\Operation\Create;

use Magento\Framework\EntityManager\Db\CreateRow;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\TypeResolver;

/**
 * Class CreateMain
 */
class CreateMain
{
    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var CreateRow
     */
    private $createRow;

    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param CreateRow $createRow
     */
    public function __construct(
        TypeResolver $typeResolver,
        HydratorPool $hydratorPool,
        CreateRow $createRow
    ) {
        $this->typeResolver = $typeResolver;
        $this->hydratorPool = $hydratorPool;
        $this->createRow = $createRow;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $arguments = array_merge($hydrator->extract($entity), $arguments);
        $entityData = $this->createRow->execute($entityType, $arguments);
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
