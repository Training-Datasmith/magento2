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
    public function operationStatusToBulkSummaryStatus(int $operationStatus): ?int
    {
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
    public function bulkSummaryStatusToOperationStatus(int $bulkStatus): array|int|null
    {
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
