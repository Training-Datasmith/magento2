<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model\Operation;

use Magento\Framework\Bulk\Bulk_Status_Interface;
use Magento\Framework\Bulk\Operation_Interface;
class Details
{
    private array $operation_cache = [];
    private $bulk_uuid;
    /**
     * Map between status codes and human readable indexes
     */
    private array $status_map = [Operation_Interface::STATUS_TYPE_COMPLETE => 'operations_successful', Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED => 'failed_retriable', Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED => 'failed_not_retriable', Operation_Interface::STATUS_TYPE_OPEN => 'open', Operation_Interface::STATUS_TYPE_REJECTED => 'rejected'];
    /**
     * Init dependencies.
     */
    public function __construct(private readonly Bulk_Status_Interface $bulk_status, $bulk_uuid = null)
    {
        $this->bulk_uuid = $bulk_uuid;
    }
    /**
     * Collect operations statistics for the bulk
     *
     * @param string $bulkUuid
     * @return array
     */
    public function get_details($bulk_uuid)
    {
        $details = ['operations_total' => 0, 'operations_successful' => 0, 'operations_failed' => 0, 'failed_retriable' => 0, 'failed_not_retriable' => 0, 'rejected' => 0];
        if (array_key_exists($bulk_uuid, $this->operation_cache)) {
            return $this->operation_cache[$bulk_uuid];
        }
        foreach ($this->status_map as $status_code => $readable_key) {
            $details[$readable_key] = $this->bulk_status->get_operations_count_by_bulk_id_and_status($bulk_uuid, $status_code);
        }
        $details['operations_total'] = array_sum($details);
        $details['operations_failed'] = $details['failed_retriable'] + $details['failed_not_retriable'];
        $this->operation_cache[$bulk_uuid] = $details;
        return $details;
    }
    /**
     * @inheritDoc
     */
    public function get_operations_total()
    {
        $this->get_details($this->bulk_uuid);
        return $this->operation_cache[$this->bulk_uuid]['operations_total'];
    }
    /**
     * @inheritDoc
     */
    public function get_open()
    {
        $this->get_details($this->bulk_uuid);
        $status_key = $this->status_map[Operation_Interface::STATUS_TYPE_OPEN];
        return $this->operation_cache[$this->bulk_uuid][$status_key];
    }
    /**
     * @inheritDoc
     */
    public function get_operations_successful()
    {
        $this->get_details($this->bulk_uuid);
        $status_key = $this->status_map[Operation_Interface::STATUS_TYPE_COMPLETE];
        return $this->operation_cache[$this->bulk_uuid][$status_key];
    }
    /**
     * @inheritDoc
     */
    public function get_total_failed()
    {
        $this->get_details($this->bulk_uuid);
        return $this->operation_cache[$this->bulk_uuid]['operations_failed'];
    }
    /**
     * @inheritDoc
     */
    public function get_failed_not_retriable()
    {
        $status_key = $this->status_map[Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED];
        return $this->operation_cache[$this->bulk_uuid][$status_key];
    }
    /**
     * @inheritDoc
     */
    public function get_failed_retriable()
    {
        $this->get_details($this->bulk_uuid);
        $status_key = $this->status_map[Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED];
        return $this->operation_cache[$this->bulk_uuid][$status_key];
    }
    /**
     * @inheritDoc
     */
    public function get_rejected()
    {
        $this->get_details($this->bulk_uuid);
        $status_key = $this->status_map[Operation_Interface::STATUS_TYPE_REJECTED];
        return $this->operation_cache[$this->bulk_uuid][$status_key];
    }
}