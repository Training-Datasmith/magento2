<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Throwable;

/**
 * Decorator for MessageController
 */
class MessageControllerDecorator
{
    public function __construct(private readonly ResourceConnection $resource, private readonly MessageController $messageController, private readonly MessageValidator $messageValidator, private readonly MessageEncoder $messageEncoder, private readonly MetadataPool $metadataPool, private readonly DateTime $dateTime)
    {
    }

    /**
     * Creates lock for provided message and update the operation start time
     */
    public function lock(EnvelopeInterface $envelope, string $consumerName): LockInterface
    {
        $operation = $this->messageEncoder->decode(AsyncConfig::SYSTEM_TOPIC_NAME, $envelope->getBody());
        $this->messageValidator->validate(AsyncConfig::SYSTEM_TOPIC_NAME, $operation);
        $metadata = $this->metadataPool->getMetadata(OperationInterface::class);
        $connection = $this->resource->getConnection($metadata->getEntityConnectionName());
        $connection->beginTransaction();
        try {
            $lock = $this->messageController->lock($envelope, $consumerName);
            $connection->update(
                $metadata->getEntityTable(),
                [
                    'started_at' => $connection->formatDate($this->dateTime->gmtTimestamp()),
                ],
                [
                    'bulk_uuid = ?' => $operation->getBulkUuid(),
                    'operation_key = ?' => $operation->getId(),
                ]
            );
            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }

        return $lock;
    }
}
