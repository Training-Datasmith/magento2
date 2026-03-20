<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface;
use Magento\Asynchronous_Operations\Model\Resource_Model\Bulk\Collection_Factory as BulkCollectionFactory;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Entity_Manager\Metadata_Pool;
/**
 * Class for bulk notification manager
 */
class Bulk_Notification_Management
{
    /**
     * BulkManagement constructor.
     */
    public function __construct(private readonly Metadata_Pool $metadata_pool, private readonly Resource_Connection $resource_connection, private readonly Bulk_Collection_Factory $bulk_collection_factory, private readonly \Psr\Log\Logger_Interface $logger)
    {
    }
    /**
     * Mark given bulks as acknowledged.
     * Notifications related to these bulks will not appear in notification area.
     *
     * @return bool true on success or false on failure
     */
    public function acknowledge_bulks(array $bulk_uuids): bool
    {
        $metadata = $this->metadata_pool->get_metadata(Bulk_Summary_Interface::class);
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        try {
            $connection->insert_array($this->resource_connection->get_table_name('magento_acknowledged_bulk'), ['bulk_uuid'], $bulk_uuids);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->get_message());
            return false;
        }
        return true;
    }
    /**
     * Remove given bulks from acknowledged list.
     * Notifications related to these bulks will appear again in notification area.
     *
     * @return bool true on success or false on failure
     */
    public function ignore_bulks(array $bulk_uuids): bool
    {
        $metadata = $this->metadata_pool->get_metadata(Bulk_Summary_Interface::class);
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        try {
            $connection->delete($this->resource_connection->get_table_name('magento_acknowledged_bulk'), ['bulk_uuid IN(?)' => $bulk_uuids]);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->get_message());
            return false;
        }
        return true;
    }
    /**
     * Retrieve all bulks that were acknowledged by given user.
     *
     * @param int $userId
     * @return BulkSummaryInterface[]
     */
    public function get_acknowledged_bulks_by_user($user_id)
    {
        return $this->bulk_collection_factory->create()->join(['acknowledged_bulk' => $this->resource_connection->get_table_name('magento_acknowledged_bulk')], 'main_table.uuid = acknowledged_bulk.bulk_uuid', [])->add_field_to_filter('user_id', $user_id)->add_order('start_time', Collection::SORT_ORDER_DESC)->get_items();
    }
    /**
     * Retrieve all bulks that were not acknowledged by given user.
     *
     * @param int $userId
     * @return BulkSummaryInterface[]
     */
    public function get_ignored_bulks_by_user($user_id)
    {
        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection $bulkCollection */
        $bulk_collection = $this->bulk_collection_factory->create();
        $bulk_collection->get_select()->join_left(['acknowledged_bulk' => $this->resource_connection->get_table_name('magento_acknowledged_bulk')], 'main_table.uuid = acknowledged_bulk.bulk_uuid', ['acknowledged_bulk.bulk_uuid']);
        return $bulk_collection->add_field_to_filter('user_id', $user_id)->add_field_to_filter('acknowledged_bulk.bulk_uuid', ['null' => true])->add_order('start_time', Collection::SORT_ORDER_DESC)->get_items();
    }
}