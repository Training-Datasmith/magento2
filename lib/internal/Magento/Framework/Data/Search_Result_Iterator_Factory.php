<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

/**
 * Class SearchResultIteratorFactory
 */
class Search_Result_Iterator_Factory
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
     * Create SearchResultIterator object
     *
     * @param string $className
     * @param array $arguments
     * @return SearchResultIterator
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($class_name, array $arguments = [])
    {
        $result_iterator = $this->object_manager->create($class_name, $arguments);
        if (!$result_iterator instanceof \Traversable) {
            throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('%1 should be an iterator', [$class_name]));
        }
        return $result_iterator;
    }
}