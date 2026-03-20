<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Collection;

/**
 * Model object factory
 */
class Model_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Create new model object
     *
     * @param string $model
     * @param array $data
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function create($model, array $data = [])
    {
        $model_instance = $this->_object_manager->create($model, $data);
        if (false == $model_instance instanceof \Magento\Framework\Model\Abstract_Model) {
            throw new \InvalidArgumentException($model . ' is not instance of \Magento\Framework\Model\AbstractModel');
        }
        return $model_instance;
    }
}