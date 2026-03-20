<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Ddl;

/**
 * @api
 */
class Trigger_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @var string
     */
    public const INSTANCE_NAME = \Magento\Framework\DB\Ddl\Trigger::class;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\DB\Ddl\Trigger
     */
    public function create(array $data = [])
    {
        return $this->object_manager->create(self::INSTANCE_NAME, $data);
    }
}