<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

/**
 * Class MapperFactory
 * @package Magento\Framework\DB
 */
class Mapper_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create Mapper object
     *
     * @param string $className
     * @param array $arguments
     * @return MapperInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($class_name, array $arguments = [])
    {
        $mapper = $this->object_manager->create($class_name, $arguments);
        if (!$mapper instanceof Mapper_Interface) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('%1 doesn\'t implement \Magento\Framework\DB\MapperInterface', [$class_name]));
        }
        return $mapper;
    }
}