<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp;

use Closure;
use Exception;
use Magento\Framework\Message_Queue\Connection_Lost_Exception;
use Magento\Framework\Message_Queue\Envelope_Factory;
use Magento\Framework\Message_Queue\Envelope_Interface;
use Magento\Framework\Message_Queue\Queue_Interface;
use Magento\Framework\Phrase;
use Php_Amqp_Lib\Message\Amqp_Message;
use Psr\Log\Logger_Interface;
/**
 * @api
 * @since 103.0.0
 */
class Queue implements Queue_Interface
{
    /**
     * @var Config
     */
    private $amqp_config;
    /**
     * @var string
     */
    private $queue_name;
    /**
     * @var EnvelopeFactory
     */
    private $envelope_factory;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * The prefetch value is used to specify how many messages that are being sent to the consumer at the same time.
     * @see https://www.rabbitmq.com/consumer-prefetch.html
     * @var int
     */
    private $prefetch_count;
    /**
     * Initialize dependencies.
     *
     * @param Config $amqpConfig
     * @param EnvelopeFactory $envelopeFactory
     * @param string $queueName
     * @param LoggerInterface $logger
     * @param int $prefetchCount
     */
    public function __construct(Config $amqp_config, Envelope_Factory $envelope_factory, $queue_name, Logger_Interface $logger, $prefetch_count = 100)
    {
        $this->amqp_config = $amqp_config;
        $this->queue_name = $queue_name;
        $this->envelope_factory = $envelope_factory;
        $this->logger = $logger;
        $this->prefetch_count = (int) $prefetch_count;
    }
    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function dequeue()
    {
        $envelope = null;
        $channel = $this->amqp_config->get_channel();
        // @codingStandardsIgnoreStart
        /** @var AMQPMessage $message */
        try {
            $message = $channel->basic_get($this->queue_name);
        } catch (Exception $exception) {
            throw new Connection_Lost_Exception($exception->get_message(), $exception->get_code(), $exception);
        }
        if ($message !== null) {
            $properties = array_merge($message->get_properties(), ['topic_name' => $message->delivery_info['routing_key'], 'delivery_tag' => $message->delivery_info['delivery_tag']]);
            $envelope = $this->envelope_factory->create(['body' => $message->body, 'properties' => $properties]);
        }
        // @codingStandardsIgnoreEnd
        return $envelope;
    }
    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function acknowledge(Envelope_Interface $envelope)
    {
        $properties = $envelope->get_properties();
        $channel = $this->amqp_config->get_channel();
        // @codingStandardsIgnoreStart
        try {
            $channel->basic_ack($properties['delivery_tag']);
        } catch (Exception $exception) {
            throw new Connection_Lost_Exception($exception->get_message(), $exception->get_code(), $exception);
        }
        // @codingStandardsIgnoreEnd
    }
    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function subscribe($callback)
    {
        $callback_converter = function (Amqp_Message $message) use ($callback) {
            // @codingStandardsIgnoreStart
            $properties = array_merge($message->get_properties(), ['topic_name' => $message->delivery_info['routing_key'], 'delivery_tag' => $message->delivery_info['delivery_tag']]);
            // @codingStandardsIgnoreEnd
            $envelope = $this->envelope_factory->create(['body' => $message->body, 'properties' => $properties]);
            if ($callback instanceof Closure) {
                $callback($envelope);
            } else {
                call_user_func($callback, $envelope);
            }
        };
        $channel = $this->amqp_config->get_channel();
        // @codingStandardsIgnoreStart
        $channel->basic_qos(0, $this->prefetch_count, false);
        $channel->basic_consume($this->queue_name, '', false, false, false, false, $callback_converter);
        // @codingStandardsIgnoreEnd
        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function reject(Envelope_Interface $envelope, $requeue = true, $rejection_message = null)
    {
        $properties = $envelope->get_properties();
        $channel = $this->amqp_config->get_channel();
        // @codingStandardsIgnoreStart
        $channel->basic_reject($properties['delivery_tag'], $requeue);
        // @codingStandardsIgnoreEnd
        if ($rejection_message !== null) {
            $this->logger->critical(new Phrase('Message has been rejected: %message', ['message' => $rejection_message]));
        }
    }
    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function push(Envelope_Interface $envelope)
    {
        $message_properties = $envelope->get_properties();
        $msg = new Amqp_Message($envelope->get_body(), ['correlation_id' => $message_properties['correlation_id'], 'delivery_mode' => 2]);
        $this->amqp_config->get_channel()->basic_publish($msg, '', $this->queue_name);
        return $msg;
    }
    /**
     * Only subscribe queue
     *
     * @return void
     */
    public function subscribe_queue(): void
    {
        throw new \BadMethodCallException('subscribeQueue is not supported in amqp queue.');
    }
    /**
     * Clear queue
     *
     * @return int
     */
    public function clear_queue(): int
    {
        throw new \BadMethodCallException('clearQueue is not supported in amqp queue.');
    }
    /**
     * Get connection name
     *
     * @return string
     */
    public function get_connection_name(): string
    {
        return $this->amqp_config->get_connection_name();
    }
}