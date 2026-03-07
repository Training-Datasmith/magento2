<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Ui\Component\Operation;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollectionFactory,
        private readonly \Magento\AsynchronousOperations\Model\Operation\Details $operationDetails,
        private readonly \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $bulkCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Human readable summary for bulk
     *
     * @param array $operationDetails structure is implied as getOperationDetails() result
     * @return string
     */
    private function getSummaryReport(array $operationDetails)
    {
        if (0 == $operationDetails['operations_successful'] && 0 == $operationDetails['operations_failed']) {
            return __('Pending, in queue...');
        }

        $summaryReport = __('%1 items selected for mass update', $operationDetails['operations_total'])->__toString();
        if ($operationDetails['operations_successful'] > 0) {
            $summaryReport .= __(', %1 successfully updated', $operationDetails['operations_successful']);
        }

        if ($operationDetails['operations_failed'] > 0) {
            $summaryReport .= __(', %1 failed to update', $operationDetails['operations_failed']);
        }

        return $summaryReport;
    }

    /**
     * Bulk summary with operation statistics
     */
    public function getData(): array
    {
        $data = [];
        $items = $this->collection->getItems();
        if (count($items) == 0) {
            return $data;
        }
        $bulk = array_shift($items);
        /** @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface $bulk */
        $data = $bulk->getData();
        $operationDetails = $this->operationDetails->getDetails($data['uuid']);
        $data['summary'] = $this->getSummaryReport($operationDetails);
        $data = array_merge($data, $operationDetails);

        return [$bulk->getBulkId() => $data];
    }

    /**
     * Prepares Meta
     */
    public function prepareMeta(array $meta): array
    {
        $requestId = $this->request->getParam($this->requestFieldName);
        $operationDetails = $this->operationDetails->getDetails($requestId);

        if (isset($operationDetails['failed_retriable']) && !$operationDetails['failed_retriable']) {
            $meta['retriable_operations']['arguments']['data']['disabled'] = true;
        }

        if (isset($operationDetails['failed_not_retriable']) && !$operationDetails['failed_not_retriable']) {
            $meta['failed_operations']['arguments']['data']['disabled'] = true;
        }

        return $meta;
    }
}
