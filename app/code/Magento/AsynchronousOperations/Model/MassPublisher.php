<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;
use Magento\Framework\MessageQueue\Bulk\ExchangeRepository;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageIdGeneratorInterface;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Class MassPublisher used for encoding topic entities to OperationInterface and publish them.
 */
class MassPublisher implements PublisherInterface
{
    /**
     * Initialize dependencies.
     */
    public function __construct(private readonly ExchangeRepository $exchangeRepository, private readonly EnvelopeFactory $envelopeFactory, private readonly MessageEncoder $messageEncoder, private readonly MessageValidator $messageValidator, private readonly PublisherConfig $publisherConfig, private readonly MessageIdGeneratorInterface $messageIdGenerator)
    {
    }

    /**
     * @inheritdoc
     */
    public function publish($topicName, $data): null
    {
        $envelopes = [];
        foreach ($data as $message) {
            $this->messageValidator->validate(AsyncConfig::SYSTEM_TOPIC_NAME, $message);
            $message = $this->messageEncoder->encode(AsyncConfig::SYSTEM_TOPIC_NAME, $message);
            $envelopes[] = $this->envelopeFactory->create(
                [
                    'body' => $message,
                    'properties' => [
                        'topic_name' => $topicName,
                        'delivery_mode' => 2,
                        'message_id' => $this->messageIdGenerator->generate($topicName),
                    ],
                ]
            );
        }
        $publisher = $this->publisherConfig->getPublisher($topicName);
        $connectionName = $publisher->getConnection()->getName();
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $exchange->enqueue($topicName, $envelopes);
        return null;
    }
}
