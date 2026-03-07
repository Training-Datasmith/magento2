<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\MessageQueue\Rpc;

class ResponseQueueNameBuilder
{
    /**
     * Response queue name prefix
     */
    public const RESPONSE_QUEUE_PREFIX = 'responseQueue.';

    /**
     * Get response queue name.
     *
     * @param string $topicName
     * @return string
     */
    public function getQueueName($topicName)
    {
        return self::RESPONSE_QUEUE_PREFIX . str_replace('-', '_', $topicName);
    }
}
