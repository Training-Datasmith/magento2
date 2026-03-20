<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

/**
 * Class CompositeMapper
 */
class Composite_Mapper implements Mapper_Interface
{
    /**
     * @var MapperInterface[]
     */
    private $mappers;
    /**
     * @param MapperInterface[] $mappers
     */
    public function __construct($mappers)
    {
        $this->mappers = $mappers;
    }
    /**
     * {@inheritdoc}
     */
    public function entity_to_database($entity_type, $data)
    {
        foreach ($this->mappers as $mapper) {
            $data = $mapper->entity_to_database($entity_type, $data);
        }
        return $data;
    }
    /**
     * {@inheritdoc}
     */
    public function database_to_entity($entity_type, $data)
    {
        foreach ($this->mappers as $mapper) {
            $data = $mapper->database_to_entity($entity_type, $data);
        }
        return $data;
    }
}