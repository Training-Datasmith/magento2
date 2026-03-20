<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model\Resource_Model\Operation;

use Magento\Asynchronous_Operations\Api\Data\Operation_Interface;
use Magento\Asynchronous_Operations\Api\Data\Operation_Interface_Factory;
use Magento\Asynchronous_Operations\Model\Operation_Repository_Interface;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Message_Queue\Message_Encoder;
use Magento\Framework\Message_Queue\Message_Validator;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Create operation for list of bulk operations.
 */
class Operation_Repository implements Operation_Repository_Interface
{
    /**
     * @var OperationInterfaceFactory
     */
    private $operation_factory;
    public function __construct(Operation_Interface_Factory $operation_factory, private readonly Message_Validator $message_validator, private readonly Message_Encoder $message_encoder, private readonly Json $json_serializer)
    {
        $this->operation_factory = $operation_factory;
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
    public function create_by_topic($topic_name, $entity_params, $group_id, $operation_id)
    {
        $this->message_validator->validate($topic_name, $entity_params);
        $encoded_message = $this->message_encoder->encode($topic_name, $entity_params);
        $serialized_data = ['entity_id' => null, 'entity_link' => '', 'meta_information' => $encoded_message];
        $data = ['data' => [Operation_Interface::ID => $operation_id, Operation_Interface::BULK_ID => $group_id, Operation_Interface::TOPIC_NAME => $topic_name, Operation_Interface::SERIALIZED_DATA => $this->json_serializer->serialize($serialized_data), Operation_Interface::STATUS => Operation_Interface::STATUS_TYPE_OPEN]];
        /** @var OperationInterface $operation */
        $operation = $this->operation_factory->create($data);
        return $operation;
    }
    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function create($topic_name, $entity_params, $group_id, $operation_id): Operation_Interface
    {
        return $this->create_by_topic($topic_name, $entity_params, $group_id, $operation_id);
    }
}