<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model\BulkDescription;

use Magento\Framework\Bulk\BulkSummaryInterface;

/**
 * Class for grid options
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory
     */
    private $bulkCollectionFactory;

    /**
     * Options constructor.
     */
    public function __construct(
        \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollection,
        private readonly \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->bulkCollectionFactory = $bulkCollection;
    }

    /**
     * {@inheritdoc}
     * @return array{value: mixed, label: mixed}[]
     */
    public function toOptionArray(): array
    {
        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection $collection */
        $collection = $this->bulkCollectionFactory->create();

        /** @var \Magento\Framework\DB\Select $select */
        $select = $collection->getSelect();
        $select->reset();
        $select->distinct(true);
        $select->from($collection->getMainTable(), ['description']);
        $select->where('user_id = ?', $this->userContext->getUserId());

        $options = [];

        /** @var BulkSummaryInterface $item */
        foreach ($collection->getItems() as $item) {
            $options[] = [
                'value' => $item->getDescription(),
                'label' => $item->getDescription(),
            ];
        }
        return $options;
    }
}
