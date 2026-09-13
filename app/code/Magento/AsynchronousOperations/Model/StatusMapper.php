<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\Bulk\BulkSummaryInterface;
use Magento\Framework\Bulk\OperationInterface;

/**
 * Class StatusMapper
 */
class StatusMapper
{
    /**
     * Map operation status to bulk summary status
     */
    public function operationStatusToBulkSummaryStatus(int|string|null $operationStatus): ?int
    {
        if ($operationStatus === null || (!is_int($operationStatus) && !is_numeric($operationStatus))) {
            return null;
        }
        $operationStatus = (int) $operationStatus;
        $statusMapping = [
            OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED => BulkSummaryInterface::FINISHED_WITH_FAILURE,
            OperationInterface::STATUS_TYPE_RETRIABLY_FAILED => BulkSummaryInterface::FINISHED_WITH_FAILURE,
            OperationInterface::STATUS_TYPE_REJECTED => BulkSummaryInterface::FINISHED_WITH_FAILURE,
            OperationInterface::STATUS_TYPE_COMPLETE => BulkSummaryInterface::FINISHED_SUCCESSFULLY,
            OperationInterface::STATUS_TYPE_OPEN => BulkSummaryInterface::IN_PROGRESS,
            BulkSummaryInterface::NOT_STARTED => BulkSummaryInterface::NOT_STARTED,
        ];
        return $statusMapping[$operationStatus] ?? null;
    }

    /**
     * Map bulk summary status to operation status
     *
     * @return int|null
     */
    public function bulkSummaryStatusToOperationStatus(int|string|null $bulkStatus): array|int|null
    {
        if ($bulkStatus === null || (!is_int($bulkStatus) && !is_numeric($bulkStatus))) {
            return null;
        }
        $bulkStatus = (int) $bulkStatus;
        $statusMapping = [
            BulkSummaryInterface::FINISHED_WITH_FAILURE => [
                OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
                OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
                OperationInterface::STATUS_TYPE_REJECTED,
            ],
            BulkSummaryInterface::FINISHED_SUCCESSFULLY => OperationInterface::STATUS_TYPE_COMPLETE,
            BulkSummaryInterface::IN_PROGRESS => OperationInterface::STATUS_TYPE_OPEN,
            BulkSummaryInterface::NOT_STARTED => BulkSummaryInterface::NOT_STARTED,
        ];
        return $statusMapping[$bulkStatus] ?? null;
    }
}
