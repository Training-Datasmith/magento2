<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

/**
 * Class QueryFactory
 */
class Query_Factory
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
     * Create Query object
     *
     * @param string $className
     * @param array $arguments
     * @return QueryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($class_name, array $arguments = [])
    {
        $query = $this->object_manager->create($class_name, $arguments);
        if (!$query instanceof Query_Interface) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('%1 doesn\'t implement \Magento\Framework\DB\QueryInterface', [$class_name]));
        }
        return $query;
    }
}