<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

class Currency_Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_object_manager = null;
    /**
     * @var string
     */
    protected $_instance_name = null;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(Object_Manager_Interface $object_manager, $instance_name = Currency_Interface::class)
    {
        $this->_object_manager = $object_manager;
        $this->_instance_name = $instance_name;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return CurrencyInterface
     */
    public function create(array $data = [])
    {
        return $this->_object_manager->create($this->_instance_name, $data);
    }
}