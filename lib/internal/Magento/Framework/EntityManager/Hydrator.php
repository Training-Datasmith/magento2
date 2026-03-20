<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

use Magento\Framework\Api\Data_Object_Helper;
use Magento\Framework\Reflection\Data_Object_Processor;
/**
 * Class Hydrator
 */
class Hydrator implements Hydrator_Interface
{
    /**
     * @var DataObjectProcessor
     */
    private $data_object_processor;
    /**
     * @var DataObjectHelper
     */
    private $data_object_helper;
    /**
     * @var TypeResolver
     */
    private $type_resolver;
    /**
     * @var MapperPool
     */
    private $mapper_pool;
    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param TypeResolver $typeResolver
     * @param MapperPool $mapperPool
     */
    public function __construct(Data_Object_Processor $data_object_processor, Data_Object_Helper $data_object_helper, Type_Resolver $type_resolver, Mapper_Pool $mapper_pool)
    {
        $this->data_object_processor = $data_object_processor;
        $this->data_object_helper = $data_object_helper;
        $this->type_resolver = $type_resolver;
        $this->mapper_pool = $mapper_pool;
    }
    /**
     * {@inheritdoc}
     */
    public function extract($entity)
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $data = $this->data_object_processor->build_output_data_array($entity, $entity_type);
        $mapper = $this->mapper_pool->get_mapper($entity_type);
        return $mapper->entity_to_database($entity_type, $data);
    }
    /**
     * {@inheritdoc}
     */
    public function hydrate($entity, array $data)
    {
        $entity_type = $this->type_resolver->resolve($entity);
        $mapper = $this->mapper_pool->get_mapper($entity_type);
        $data = $mapper->database_to_entity($entity_type, array_merge($this->extract($entity), $data));
        $this->data_object_helper->populate_with_array($entity, $data, $entity_type);
        return $entity;
    }
}