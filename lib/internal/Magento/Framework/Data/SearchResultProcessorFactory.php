<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

/**
 * Class SearchResultProcessorFactory
 */
class Search_Result_Processor_Factory
{
    public const DEFAULT_INSTANCE_NAME = \Magento\Framework\Data\Search_Result_Processor::class;
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create class instance with specified parameters
     *
     * @param AbstractSearchResult $collection
     * @return SearchResultProcessor
     */
    public function create(Abstract_Search_Result $collection)
    {
        return $this->object_manager->create(static::DEFAULT_INSTANCE_NAME, ['searchResult' => $collection]);
    }
}