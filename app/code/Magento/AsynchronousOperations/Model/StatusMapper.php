<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Model;

use Magento\Framework\Bulk\Bulk_Summary_Interface;
use Magento\Framework\Bulk\Operation_Interface;
/**
 * Class StatusMapper
 */
class Status_Mapper
{
    /**
     * Map operation status to bulk summary status
     */
    public function operation_status_to_bulk_summary_status(int $operation_status): ?int
    {
        $status_mapping = [Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED => Bulk_Summary_Interface::FINISHED_WITH_FAILURE, Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED => Bulk_Summary_Interface::FINISHED_WITH_FAILURE, Operation_Interface::STATUS_TYPE_REJECTED => Bulk_Summary_Interface::FINISHED_WITH_FAILURE, Operation_Interface::STATUS_TYPE_COMPLETE => Bulk_Summary_Interface::FINISHED_SUCCESSFULLY, Operation_Interface::STATUS_TYPE_OPEN => Bulk_Summary_Interface::IN_PROGRESS, Bulk_Summary_Interface::NOT_STARTED => Bulk_Summary_Interface::NOT_STARTED];
        return $status_mapping[$operation_status] ?? null;
    }
    /**
     * Map bulk summary status to operation status
     *
     * @return int|null
     */
    public function bulk_summary_status_to_operation_status(int $bulk_status): array|int|null
    {
        $status_mapping = [Bulk_Summary_Interface::FINISHED_WITH_FAILURE => [Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED, Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED, Operation_Interface::STATUS_TYPE_REJECTED], Bulk_Summary_Interface::FINISHED_SUCCESSFULLY => Operation_Interface::STATUS_TYPE_COMPLETE, Bulk_Summary_Interface::IN_PROGRESS => Operation_Interface::STATUS_TYPE_OPEN, Bulk_Summary_Interface::NOT_STARTED => Bulk_Summary_Interface::NOT_STARTED];
        return $status_mapping[$bulk_status] ?? null;
    }
}