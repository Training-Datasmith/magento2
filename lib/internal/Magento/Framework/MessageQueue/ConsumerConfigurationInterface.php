<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

/**
 * Configuration for the consumer interface
 *
 * @api
 */
interface ConsumerConfigurationInterface
{
    public const CONSUMER_NAME = 'consumer_name';

    public const QUEUE_NAME = 'queue_name';
    public const MAX_MESSAGES = 'max_messages';
    public const SCHEMA_TYPE = 'schema_type';
    public const TOPICS = 'topics';
    public const TOPIC_TYPE = 'consumer_type';
    public const TOPIC_HANDLERS = 'handlers';
    public const MAX_IDLE_TIME = 'max_idle_time';
    public const SLEEP = 'sleep';
    public const ONLY_SPAWN_WHEN_MESSAGE_AVAILABLE = 'only_spawn_when_message_available';

    public const TYPE_SYNC = 'sync';
    public const TYPE_ASYNC = 'async';
    public const INSTANCE_TYPE_BATCH = 'batch';
    public const INSTANCE_TYPE_SINGULAR = 'singular';

    /**
     * Get consumer name.
     *
     * @return string
     */
    public function getConsumerName();

    /**
     * Get the name of queue which consumer will read from.
     *
     * @return string
     */
    public function getQueueName();

    /**
     * Get consumer type sync|async.
     *
     * @return string
     * @deprecated 103.0.0
     * @see \Magento\Framework\Communication\ConfigInterface::getTopic
     * @throws \LogicException
     */
    public function getType();

    /**
     * Get maximum number of message, which will be read by consumer before termination of the process.
     *
     * @return int|null
     */
    public function getMaxMessages();

    /**
     * Get handlers by topic type.
     *
     * @param string $topicName
     * @return callback[]
     * @throws \LogicException
     */
    public function getHandlers($topicName);

    /**
     * Get topics.
     *
     * @return string[]
     */
    public function getTopicNames();

    /**
     * Get message schema type.
     *
     * @param string $topicName
     * @return string
     */
    public function getMessageSchemaType($topicName);

    /**
     * Get message queue instance.
     *
     * @return QueueInterface
     */
    public function getQueue();

    /**
     * Get maximal time (in seconds) for waiting new messages from queue before terminating consumer.
     *
     * @return int|null
     */
    public function getMaxIdleTime();

    /**
     * Get time to sleep (in seconds) before checking if a new message is available in the queue.
     *
     * @return int|null
     */
    public function getSleep();

    /**
     * Get is consumer have to be spawned only if there are messages in the queue.
     *
     * @return boolean|null
     */
    public function getOnlySpawnWhenMessageAvailable();
}
