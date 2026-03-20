<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Ui\Component\Operation;

/**
 * Class DataProvider
 */
class Data_Provider extends \Magento\Ui\Data_Provider\Abstract_Data_Provider
{
    /**
     * @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection
     */
    protected $collection;
    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     */
    public function __construct($name, $primary_field_name, $request_field_name, \Magento\Asynchronous_Operations\Model\Resource_Model\Bulk\Collection_Factory $bulk_collection_factory, private readonly \Magento\Asynchronous_Operations\Model\Operation\Details $operation_details, private readonly \Magento\Framework\App\Request_Interface $request, array $meta = [], array $data = [])
    {
        $this->collection = $bulk_collection_factory->create();
        parent::__construct($name, $primary_field_name, $request_field_name, $meta, $data);
        $this->meta = $this->prepare_meta($this->meta);
    }
    /**
     * Human readable summary for bulk
     *
     * @param array $operationDetails structure is implied as getOperationDetails() result
     * @return string
     */
    private function get_summary_report(array $operation_details)
    {
        if (0 == $operation_details['operations_successful'] && 0 == $operation_details['operations_failed']) {
            return __('Pending, in queue...');
        }
        $summary_report = __('%1 items selected for mass update', $operation_details['operations_total'])->__toString();
        if ($operation_details['operations_successful'] > 0) {
            $summary_report .= __(', %1 successfully updated', $operation_details['operations_successful']);
        }
        if ($operation_details['operations_failed'] > 0) {
            $summary_report .= __(', %1 failed to update', $operation_details['operations_failed']);
        }
        return $summary_report;
    }
    /**
     * Bulk summary with operation statistics
     */
    public function get_data(): array
    {
        $data = [];
        $items = $this->collection->get_items();
        if (count($items) == 0) {
            return $data;
        }
        $bulk = array_shift($items);
        /** @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface $bulk */
        $data = $bulk->get_data();
        $operation_details = $this->operation_details->get_details($data['uuid']);
        $data['summary'] = $this->get_summary_report($operation_details);
        $data = array_merge($data, $operation_details);
        return [$bulk->get_bulk_id() => $data];
    }
    /**
     * Prepares Meta
     */
    public function prepare_meta(array $meta): array
    {
        $request_id = $this->request->get_param($this->request_field_name);
        $operation_details = $this->operation_details->get_details($request_id);
        if (isset($operation_details['failed_retriable']) && !$operation_details['failed_retriable']) {
            $meta['retriable_operations']['arguments']['data']['disabled'] = true;
        }
        if (isset($operation_details['failed_not_retriable']) && !$operation_details['failed_not_retriable']) {
            $meta['failed_operations']['arguments']['data']['disabled'] = true;
        }
        return $meta;
    }
}