<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Bulk_Description;

use Magento\Framework\Bulk\Bulk_Summary_Interface;
/**
 * Class for grid options
 */
class Options implements \Magento\Framework\Data\Option_Source_Interface
{
    /**
     * @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory
     */
    private $bulk_collection_factory;
    /**
     * Options constructor.
     */
    public function __construct(\Magento\Asynchronous_Operations\Model\Resource_Model\Bulk\Collection_Factory $bulk_collection, private readonly \Magento\Authorization\Model\User_Context_Interface $user_context)
    {
        $this->bulk_collection_factory = $bulk_collection;
    }
    /**
     * {@inheritdoc}
     * @return array{value: mixed, label: mixed}[]
     */
    public function to_option_array(): array
    {
        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection $collection */
        $collection = $this->bulk_collection_factory->create();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $collection->get_select();
        $select->reset();
        $select->distinct(true);
        $select->from($collection->get_main_table(), ['description']);
        $select->where('user_id = ?', $this->user_context->get_user_id());
        $options = [];
        /** @var BulkSummaryInterface $item */
        foreach ($collection->get_items() as $item) {
            $options[] = ['value' => $item->get_description(), 'label' => $item->get_description()];
        }
        return $options;
    }
}