<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Operation_Extension_Interface_Factory;
use Magento\Asynchronous_Operations\Api\Data\Operation_Search_Results_Interface_Factory as SearchResultFactory;
use Magento\Asynchronous_Operations\Model\Resource_Model\Operation\Collection_Factory;
use Magento\Framework\Api\Extension_Attribute\Join_Processor_Interface;
use Magento\Framework\Api\Search_Criteria\Collection_Processor_Interface;
/**
 * Repository class for @see \Magento\AsynchronousOperations\Api\OperationRepositoryInterface
 */
class Operation_Repository implements \Magento\Asynchronous_Operations\Api\Operation_Repository_Interface
{
    /**
     * @var CollectionFactory
     */
    private $collection_factory;
    /**
     * OperationRepository constructor.
     */
    public function __construct(Collection_Factory $collection_factory, private readonly Search_Result_Factory $search_result_factory, private readonly Join_Processor_Interface $join_processor, Operation_Extension_Interface_Factory $operation_extension, private readonly Collection_Processor_Interface $collection_processor)
    {
        $this->collection_factory = $collection_factory;
    }
    /**
     * @inheritDoc
     */
    public function get_list(\Magento\Framework\Api\Search_Criteria_Interface $search_criteria)
    {
        /** @var \Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterface $searchResult */
        $search_result = $this->search_result_factory->create();
        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection $collection */
        $collection = $this->collection_factory->create();
        $this->join_processor->process($collection, \Magento\Asynchronous_Operations\Api\Data\Operation_Interface::class);
        $this->collection_processor->process($search_criteria, $collection);
        $search_result->set_search_criteria($search_criteria);
        $search_result->set_total_count($collection->get_size());
        $search_result->set_items($collection->get_items());
        return $search_result;
    }
}