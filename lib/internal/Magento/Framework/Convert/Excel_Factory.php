<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Convert;

use Magento\Framework\Object_Manager_Interface;
class Excel_Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @var string
     */
    protected $instance_name;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(Object_Manager_Interface $object_manager, $instance_name = \Magento\Framework\Convert\Excel::class)
    {
        $this->object_manager = $object_manager;
        $this->instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\Convert\Excel
     */
    public function create(array $data = [])
    {
        return $this->object_manager->create($this->instance_name, $data);
    }
}