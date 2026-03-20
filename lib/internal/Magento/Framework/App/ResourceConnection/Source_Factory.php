<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Resource_Connection;

class Source_Factory
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
     * Get source class instance by class name
     *
     * @param string $className
     * @throws \InvalidArgumentException
     * @return SourceProviderInterface
     */
    public function create($class_name)
    {
        $source = $this->object_manager->create($class_name);
        if (!$source instanceof Source_Provider_Interface) {
            throw new \InvalidArgumentException($class_name . ' doesn\'t implement \Magento\Framework\App\ResourceConnection\SourceProviderInterface');
        }
        return $source;
    }
}