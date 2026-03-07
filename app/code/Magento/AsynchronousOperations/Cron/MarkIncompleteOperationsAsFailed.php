<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Cron;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Marks incomplete operations as failed
 */
class MarkIncompleteOperationsAsFailed
{
    /**
     * Default message maximum processing time. Default to 12h
     */
    private const DEFAULT_MESSAGE_MAX_PROCESSING_TIME = 43200;

    /**
     * Default error code
     */
    private const ERROR_CODE = 0;

    /**
     * Default error message
     */
    private const ERROR_MESSAGE = 'Unknown Error';

    public function __construct(private readonly Operation $resource, private readonly DateTime $dateTime, private readonly int $messageMaxProcessingTime = self::DEFAULT_MESSAGE_MAX_PROCESSING_TIME, private readonly int $failedStatus = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED, private readonly int $errorCode = self::ERROR_CODE, private readonly string $errorMessage = self::ERROR_MESSAGE)
    {
    }

    /**
     * Marks incomplete operations as failed
     */
    public function execute(): void
    {
        $connection = $this->resource->getConnection();
        $now = $this->dateTime->gmtTimestamp();
        $idField = $this->resource->getIdFieldName();
        $select = $connection->select()
            ->from($this->resource->getMainTable(), [$idField])
            ->where('status = ?', OperationInterface::STATUS_TYPE_OPEN)
            ->where('started_at <= ?', $connection->formatDate($now - $this->messageMaxProcessingTime));

        foreach ($connection->fetchCol($select) as $id) {
            $connection->update(
                $this->resource->getMainTable(),
                [
                    'status' => $this->failedStatus,
                    'result_message' => $this->errorMessage,
                    'error_code' => $this->errorCode,
                ],
                [
                    "$idField = ?" => (int) $id,
                ]
            );
        }
    }
}
