<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationExtensionInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterfaceFactory as SearchResultFactory;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Repository class for @see \Magento\AsynchronousOperations\Api\OperationRepositoryInterface
 */
class OperationRepository implements \Magento\AsynchronousOperations\Api\OperationRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * OperationRepository constructor.
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        private readonly SearchResultFactory $searchResultFactory,
        private readonly JoinProcessorInterface $joinProcessor,
        OperationExtensionInterfaceFactory $operationExtension,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();

        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process($collection, \Magento\AsynchronousOperations\Api\Data\OperationInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getItems());

        return $searchResult;
    }
}
