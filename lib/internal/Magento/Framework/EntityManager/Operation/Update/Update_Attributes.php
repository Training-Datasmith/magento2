<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation\Update;

use Magento\Framework\Entity_Manager\Hydrator_Pool;
use Magento\Framework\Entity_Manager\Operation\Attribute_Pool;
use Magento\Framework\Entity_Manager\Type_Resolver;
/**
 * Class UpdateAttributes
 */
class Update_Attributes
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
     * @var AttributePool
     */
    private $attribute_pool;
    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param AttributePool $attributePool
     */
    public function __construct(Type_Resolver $type_resolver, Hydrator_Pool $hydrator_pool, Attribute_Pool $attribute_pool)
    {
        $this->type_resolver = $type_resolver;
        $this->hydrator_pool = $hydrator_pool;
        $this->attribute_pool = $attribute_pool;
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
        $entity_data = array_merge($hydrator->extract($entity), $arguments);
        $actions = $this->attribute_pool->get_actions($entity_type, 'update');
        foreach ($actions as $action) {
            $entity_data = $action->execute($entity_type, $entity_data, $arguments);
        }
        $entity = $hydrator->hydrate($entity, $entity_data);
        return $entity;
    }
}