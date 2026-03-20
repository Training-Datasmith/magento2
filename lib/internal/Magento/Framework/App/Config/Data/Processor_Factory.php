<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Data;

/**
 * @api
 */
class Processor_Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @var ProcessorInterface[]
     */
    protected $_pool;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->_object_manager = $object_manager;
    }
    /**
     * Get concrete Processor Interface instance
     *
     * @param string $processorModel Classname of the instance to get
     * @return ProcessorInterface
     * @throws \InvalidArgumentException In case the given classname is not an instance of ProcessorInterface
     */
    public function get($processor_model)
    {
        if (!isset($this->_pool[$processor_model])) {
            $instance = $this->_object_manager->create($processor_model);
            if (!$instance instanceof Processor_Interface) {
                throw new \InvalidArgumentException($processor_model . ' is not instance of \Magento\Framework\App\Config\Data\ProcessorInterface');
            }
            $this->_pool[$processor_model] = $instance;
        }
        return $this->_pool[$processor_model];
    }
}