<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

use Magento\Framework\Object_Manager_Interface;
/**
 * Class HydratorPool
 */
class Hydrator_Pool
{
    /**
     * @var HydratorInterface[]
     */
    private $hydrators;
    /**
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $hydrators
     */
    public function __construct(Object_Manager_Interface $object_manager, $hydrators = [])
    {
        $this->object_manager = $object_manager;
        $this->hydrators = $hydrators;
    }
    /**
     * @param string $entityType
     * @return HydratorInterface
     */
    public function get_hydrator($entity_type)
    {
        if (isset($this->hydrators[$entity_type])) {
            return $this->object_manager->get($this->hydrators[$entity_type]);
        } else {
            return $this->object_manager->get(Hydrator_Interface::class);
        }
    }
}