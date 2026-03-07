<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;

/**
 * @deprecated 103.0.0
 */
interface ConfigInterface
{
    public const PUBLISHERS = 'publishers';
    public const PUBLISHER_NAME = 'name';
    public const PUBLISHER_CONNECTION = 'connection';
    public const PUBLISHER_EXCHANGE = 'exchange';

    public const TOPICS = 'topics';
    public const TOPIC_NAME = 'name';
    public const TOPIC_PUBLISHER = 'publisher';
    public const TOPIC_SCHEMA = 'schema';
    public const TOPIC_RESPONSE_SCHEMA = 'response_schema';
    public const TOPIC_SCHEMA_TYPE = 'schema_type';
    public const TOPIC_SCHEMA_VALUE = 'schema_value';

    public const TOPIC_SCHEMA_TYPE_OBJECT = 'object';
    public const TOPIC_SCHEMA_TYPE_METHOD = 'method_arguments';

    public const SCHEMA_METHOD_PARAM_NAME = 'param_name';
    public const SCHEMA_METHOD_PARAM_POSITION = 'param_position';
    public const SCHEMA_METHOD_PARAM_TYPE = 'param_type';
    public const SCHEMA_METHOD_PARAM_IS_REQUIRED = 'is_required';

    public const CONSUMERS = 'consumers';
    public const CONSUMER_NAME = 'name';
    public const CONSUMER_QUEUE = 'queue';
    public const CONSUMER_CONNECTION = 'connection';
    public const CONSUMER_INSTANCE_TYPE = 'instance_type';
    public const CONSUMER_CLASS = 'type';
    public const CONSUMER_METHOD = 'method';
    public const CONSUMER_MAX_MESSAGES = 'max_messages';
    public const CONSUMER_HANDLERS = 'handlers';
    public const CONSUMER_HANDLER_TYPE = 'type';
    public const CONSUMER_HANDLER_METHOD = 'method';
    public const CONSUMER_TYPE = 'consumer_type';
    public const CONSUMER_TYPE_SYNC = 'sync';
    public const CONSUMER_TYPE_ASYNC = 'async';

    public const RESPONSE_QUEUE_PREFIX = 'responseQueue.';

    public const BINDS = 'binds';
    public const BIND_QUEUE = 'queue';
    public const BIND_EXCHANGE = 'exchange';
    public const BIND_TOPIC = 'topic';

    public const BROKER_TOPIC = 'topic';
    public const BROKER_TYPE = 'type';
    public const BROKER_EXCHANGE = 'exchange';
    public const BROKER_CONSUMERS = 'consumers';
    public const BROKER_CONSUMER_NAME = 'name';
    public const BROKER_CONSUMER_QUEUE = 'queue';
    public const BROKER_CONSUMER_INSTANCE_TYPE = 'instance_type';
    public const BROKER_CONSUMER_MAX_MESSAGES = 'max_messages';
    public const BROKERS = 'brokers';

    /**
     * Map which allows optimized search of queues corresponding to the specified exchange and topic pair.
     */
    public const EXCHANGE_TOPIC_TO_QUEUES_MAP = 'exchange_topic_to_queues_map';

    /**
     * Identify configured exchange for the provided topic.
     *
     * @param string $topicName
     * @return string
     * @throws LocalizedException
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublisher
     */
    public function getExchangeByTopic($topicName);

    /**
     * Identify a list of all queue names corresponding to the specified topic (and implicitly exchange).
     *
     * @param string $topic
     * @return string[]
     * @throws LocalizedException
     * @see \Magento\Framework\MessageQueue\Topology\ConfigInterface::getQueues
     */
    public function getQueuesByTopic($topic);

    /**
     * @param string $topic
     * @return string
     * @throws LocalizedException
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublisher
     */
    public function getConnectionByTopic($topic);

    /**
     * @param string $consumer
     * @return string
     * @throws LocalizedException
     * @see \Magento\Framework\MessageQueue\Consumer\ConfigInterface::getConsumer
     */
    public function getConnectionByConsumer($consumer);

    /**
     * Identify which option is used to define message schema: data interface or service method params
     *
     * @param string $topic
     * @return string
     * @see \Magento\Framework\Communication\ConfigInterface::getTopic
     */
    public function getMessageSchemaType($topic);

    /**
     * Get all consumer names
     *
     * @return string[]
     * @see \Magento\Framework\MessageQueue\Consumer\ConfigInterface::getConsumers
     */
    public function getConsumerNames();

    /**
     * Get consumer configuration
     *
     * @param string $name
     * @return array|null
     * @see \Magento\Framework\MessageQueue\Consumer\ConfigInterface::getConsumer
     */
    public function getConsumer($name);

    /**
     * Get queue binds
     *
     * @return array
     * @see \Magento\Framework\MessageQueue\Topology\ConfigInterface::getExchanges
     */
    public function getBinds();

    /**
     * Get publishers
     *
     * @return array
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublishers
     */
    public function getPublishers();

    /**
     * Get consumers
     *
     * @return array
     * @see \Magento\Framework\MessageQueue\Consumer\ConfigInterface::getConsumers
     */
    public function getConsumers();

    /**
     * Get topic config
     *
     * @param string $name
     * @return array
     * @see \Magento\Framework\Communication\ConfigInterface::getTopic
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublisher
     */
    public function getTopic($name);

    /**
     * Get published config
     * @param string $name
     *
     * @return array
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublisher
     */
    public function getPublisher($name);

    /**
     * Get queue name for response
     *
     * @param string $topicName
     * @return string
     * @see \Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder::getQueueName
     */
    public function getResponseQueueName($topicName);
}
