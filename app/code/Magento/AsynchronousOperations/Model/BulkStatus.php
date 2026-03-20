<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface;
use Magento\Asynchronous_Operations\Api\Data\Bulk_Summary_Interface_Factory;
use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
use Magento\Asynchronous_Operations\Api\Data\Operation_Interface_Factory;
use Magento\Asynchronous_Operations\Model\Bulk_Status\Calculated_Status_Sql;
use Magento\Asynchronous_Operations\Model\Resource_Model\Operation\Collection as OperationCollection;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Bulk\Bulk_Status_Interface;
use Magento\Framework\Entity_Manager\Metadata_Pool;
/**
 * Process bulk operations status.
 */
class Bulk_Status implements Bulk_Status_Interface
{
    /**
     * @var BulkSummaryInterfaceFactory
     */
    private $bulk_collection_factory;
    /**
     * @var OperationInterfaceFactory
     */
    private $operation_collection_factory;
    /**
     * @param ResourceModel\Bulk\CollectionFactory $bulkCollection
     * @param ResourceModel\Operation\CollectionFactory $operationCollection
     */
    public function __construct(Resource_Model\Bulk\Collection_Factory $bulk_collection, Resource_Model\Operation\Collection_Factory $operation_collection, private readonly Resource_Connection $resource_connection, private readonly Calculated_Status_Sql $calculated_status_sql, private readonly Metadata_Pool $metadata_pool)
    {
        $this->bulk_collection_factory = $bulk_collection;
        $this->operation_collection_factory = $operation_collection;
    }
    /**
     * @inheritDoc
     */
    public function get_failed_operations_by_bulk_id($bulk_uuid, $failure_type = null)
    {
        $failure_codes = $failure_type ? [$failure_type] : [Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED, Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED];
        return $this->operation_collection_factory->create()->add_field_to_filter('bulk_uuid', $bulk_uuid)->add_field_to_filter('status', $failure_codes)->get_items();
    }
    /**
     * @inheritDoc
     */
    public function get_operations_count_by_bulk_id_and_status($bulk_uuid, $status)
    {
        /** @var OperationCollection $operationCollection */
        $operation_collection = $this->operation_collection_factory->create();
        if ($status === Operation_Interface::STATUS_TYPE_OPEN) {
            $all_processed_operations_qty = $operation_collection->add_field_to_filter('bulk_uuid', $bulk_uuid)->get_size();
            if (empty($all_processed_operations_qty)) {
                return $this->get_operation_count($bulk_uuid);
            }
            $operation_collection->clear();
        }
        return $operation_collection->add_field_to_filter('bulk_uuid', $bulk_uuid)->add_field_to_filter('status', $status)->get_size();
    }
    /**
     * @inheritDoc
     */
    public function get_bulks_by_user($user_id)
    {
        /** @var ResourceModel\Bulk\Collection $collection */
        $collection = $this->bulk_collection_factory->create();
        $operation_table_name = $this->resource_connection->get_table_name('magento_operation');
        $statuses_array = [Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED, Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED, Bulk_Summary_Interface::NOT_STARTED, Operation_Interface::STATUS_TYPE_OPEN, Operation_Interface::STATUS_TYPE_COMPLETE];
        $select = $collection->get_select();
        $select->columns(['status' => $this->calculated_status_sql->get($operation_table_name)])->order(new \Zend_Db_Expr('FIELD(status, ' . implode(',', $statuses_array) . ')'));
        $collection->add_field_to_filter('user_id', $user_id)->add_order('start_time');
        return $collection->get_items();
    }
    /**
     * @inheritDoc
     */
    public function get_bulk_status($bulk_uuid): int
    {
        /**
         * Number of operations that has been processed (i.e. operations with any status but 'open')
         */
        $all_processed_operations_qty = (int) $this->operation_collection_factory->create()->add_field_to_filter('bulk_uuid', $bulk_uuid)->get_size();
        if ($all_processed_operations_qty == 0) {
            return Bulk_Summary_Interface::NOT_STARTED;
        }
        /**
         * Total number of operations that has been scheduled within the given bulk
         */
        $all_operations_qty = $this->get_operation_count($bulk_uuid);
        /**
         * Number of operations that has not been started yet (i.e. operations with status 'open')
         */
        $all_open_operations_qty = $all_operations_qty - $all_processed_operations_qty;
        /**
         * Number of operations that has been completed successfully
         */
        $all_complete_operations_qty = $this->operation_collection_factory->create()->add_field_to_filter('bulk_uuid', $bulk_uuid)->add_field_to_filter('status', Operation_Interface::STATUS_TYPE_COMPLETE)->get_size();
        if ($all_complete_operations_qty == $all_operations_qty) {
            return Bulk_Summary_Interface::FINISHED_SUCCESSFULLY;
        }
        if ($all_open_operations_qty > 0 && $all_open_operations_qty !== $all_operations_qty) {
            return Bulk_Summary_Interface::IN_PROGRESS;
        }
        return Bulk_Summary_Interface::FINISHED_WITH_FAILURE;
    }
    /**
     * Get total number of operations that has been scheduled within the given bulk.
     *
     * @param string $bulkUuid
     */
    private function get_operation_count($bulk_uuid): int
    {
        $metadata = $this->metadata_pool->get_metadata(Bulk_Summary_Interface::class);
        $connection = $this->resource_connection->get_connection_by_name($metadata->get_entity_connection_name());
        return (int) $connection->fetch_one($connection->select()->from($metadata->get_entity_table(), 'operation_count')->where('uuid = ?', $bulk_uuid));
    }
}