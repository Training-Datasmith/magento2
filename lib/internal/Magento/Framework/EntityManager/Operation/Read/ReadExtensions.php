<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Operation\Read;

use Magento\Framework\Entity_Manager\Operation\Extension_Pool;
use Magento\Framework\Entity_Manager\Type_Resolver;
/**
 * Class ReadExtensions
 */
class Read_Extensions
{
    /**
     * @var TypeResolver
     */
    private $type_resolver;
    /**
     * @var ExtensionPool
     */
    private $extension_pool;
    /**
     * @param TypeResolver $typeResolver
     * @param ExtensionPool $extensionPool
     */
    public function __construct(Type_Resolver $type_resolver, Extension_Pool $extension_pool)
    {
        $this->type_resolver = $type_resolver;
        $this->extension_pool = $extension_pool;
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $actions = $this->extension_pool->get_actions($entity_type, 'read');
        foreach ($actions as $action) {
            $entity = $action->execute($entity, $arguments);
        }
        return $entity;
    }
}