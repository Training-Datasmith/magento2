<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Collection;

class Entity_Factory implements Entity_Factory_Interface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager = null;
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param string $className
     * @param array $data
     * @throws \LogicException
     * @return \Magento\Framework\DataObject
     */
    public function create($class_name, array $data = [])
    {
        $model = $this->_object_manager->create($class_name, $data);
        //TODO: fix that when this factory used only for \Magento\Framework\Model\AbstractModel
        //if (!$model instanceof \Magento\Framework\Model\AbstractModel) {
        //    throw new \LogicException($className . ' doesn\'t implement \Magento\Framework\Model\AbstractModel');
        //}
        return $model;
    }
}