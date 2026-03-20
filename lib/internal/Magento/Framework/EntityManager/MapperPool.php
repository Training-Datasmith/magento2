<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

use Magento\Framework\Object_Manager_Interface;
/**
 * Class MapperPool
 */
class Mapper_Pool
{
    /**
     * @var string[]
     */
    private $mappers;
    /**
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $mappers
     */
    public function __construct(Object_Manager_Interface $object_manager, $mappers = [])
    {
        $this->object_manager = $object_manager;
        $this->mappers = $mappers;
    }
    /**
     * Get mapper for entity type
     * @param string $entityType
     * @return MapperInterface
     */
    public function get_mapper($entity_type)
    {
        $class_name = isset($this->mappers[$entity_type]) ? $this->mappers[$entity_type] : Mapper_Interface::class;
        return $this->object_manager->get($class_name);
    }
}