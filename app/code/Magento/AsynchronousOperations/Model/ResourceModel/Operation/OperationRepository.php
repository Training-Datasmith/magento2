<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\OperationRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Create operation for list of bulk operations.
 */
class OperationRepository implements OperationRepositoryInterface
{
    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    public function __construct(
        OperationInterfaceFactory $operationFactory,
        private readonly MessageValidator $messageValidator,
        private readonly MessageEncoder $messageEncoder,
        private readonly Json $jsonSerializer
    ) {
        $this->operationFactory = $operationFactory;
    }

    /**
     * Create operation by topic, parameters and group ID
     *
     * @param string $topicName
     * @param array $entityParams
     * @param string $groupId
     * @param string $operationId
     * @return OperationInterface
     * @throws LocalizedException
     * @deprecated 100.4.0 No longer used.
     * @see create()
     */
    public function createByTopic($topicName, $entityParams, $groupId, $operationId)
    {
        $this->messageValidator->validate($topicName, $entityParams);
        $encodedMessage = $this->messageEncoder->encode($topicName, $entityParams);

        $serializedData = [
            'entity_id'        => null,
            'entity_link'      => '',
            'meta_information' => $encodedMessage,
        ];
        $data = [
            'data' => [
                OperationInterface::ID => $operationId,
                OperationInterface::BULK_ID => $groupId,
                OperationInterface::TOPIC_NAME => $topicName,
                OperationInterface::SERIALIZED_DATA => $this->jsonSerializer->serialize($serializedData),
                OperationInterface::STATUS => OperationInterface::STATUS_TYPE_OPEN,
            ],
        ];

        /** @var OperationInterface $operation */
        $operation = $this->operationFactory->create($data);
        return $operation;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function create($topicName, $entityParams, $groupId, $operationId): OperationInterface
    {
        return $this->createByTopic($topicName, $entityParams, $groupId, $operationId);
    }
}
