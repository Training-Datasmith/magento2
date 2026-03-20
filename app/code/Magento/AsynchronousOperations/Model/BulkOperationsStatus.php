<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Asynchronous_Operations\Api\Bulk_Status_Interface;
use Magento\Asynchronous_Operations\Api\Data\Bulk_Operations_Status_Interface;
use Magento\Asynchronous_Operations\Api\Data\Bulk_Operations_Status_Interface_Factory as BulkStatusShortFactory;
use Magento\Asynchronous_Operations\Api\Data\Detailed_Bulk_Operations_Status_Interface;
use Magento\Asynchronous_Operations\Api\Data\Detailed_Bulk_Operations_Status_Interface_Factory as BulkStatusDetailedFactory;
use Magento\Asynchronous_Operations\Model\Resource_Model\Operation\Collection_Factory as OperationCollectionFactory;
use Magento\Framework\Entity_Manager\Entity_Manager;
use Magento\Framework\Exception\No_Such_Entity_Exception;
/**
 * Process bulk operations status.
 */
class Bulk_Operations_Status implements Bulk_Status_Interface
{
    public function __construct(private readonly Bulk_Status $bulk_status, private readonly Operation_Collection_Factory $operation_collection_factory, private readonly Bulk_Status_Detailed_Factory $bulk_detailed_factory, private readonly Bulk_Status_Short_Factory $bulk_short_factory, private readonly Entity_Manager $entity_manager)
    {
    }
    /**
     * @inheritDoc
     */
    public function get_failed_operations_by_bulk_id($bulk_uuid, $failure_type = null)
    {
        return $this->bulk_status->get_failed_operations_by_bulk_id($bulk_uuid, $failure_type);
    }
    /**
     * @inheritDoc
     */
    public function get_operations_count_by_bulk_id_and_status($bulk_uuid, $status)
    {
        return $this->operation_collection_factory->create()->add_field_to_filter('bulk_uuid', $bulk_uuid)->add_field_to_filter('status', $status)->get_size();
    }
    /**
     * @inheritDoc
     */
    public function get_bulks_by_user($user_id)
    {
        return $this->bulk_status->get_bulks_by_user($user_id);
    }
    /**
     * @inheritDoc
     */
    public function get_bulk_status($bulk_uuid)
    {
        return $this->bulk_status->get_bulk_status($bulk_uuid);
    }
    /**
     * @inheritDoc
     */
    public function get_bulk_detailed_status($bulk_uuid)
    {
        $bulk_summary = $this->bulk_detailed_factory->create();
        /** @var DetailedBulkOperationsStatusInterface $bulk */
        $bulk = $this->entity_manager->load($bulk_summary, $bulk_uuid);
        if ($bulk->get_bulk_id() === null) {
            throw new No_Such_Entity_Exception(__('Bulk uuid %bulkUuid not exist', ['bulkUuid' => $bulk_uuid]));
        }
        $operations = $this->operation_collection_factory->create()->add_field_to_filter('bulk_uuid', $bulk_uuid)->get_items();
        $bulk->set_operations_list($operations);
        return $bulk;
    }
    /**
     * @inheritDoc
     */
    public function get_bulk_short_status($bulk_uuid)
    {
        $bulk_summary = $this->bulk_short_factory->create();
        /** @var BulkOperationsStatusInterface $bulk */
        $bulk = $this->entity_manager->load($bulk_summary, $bulk_uuid);
        if ($bulk->get_bulk_id() === null) {
            throw new No_Such_Entity_Exception(__('Bulk uuid %bulkUuid not exist', ['bulkUuid' => $bulk_uuid]));
        }
        $operations = $this->operation_collection_factory->create()->add_field_to_filter('bulk_uuid', $bulk_uuid)->get_items();
        $bulk->set_operations_list($operations);
        return $bulk;
    }
}