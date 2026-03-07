<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Amqp;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeInterface;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class message exchange.
 *
 * @api
 * @since 103.0.0
 */
class Exchange implements ExchangeInterface
{
    public const RPC_CONNECTION_TIMEOUT = 30;

    /**
     * @var Config
     */
    private $amqpConfig;

    /**
     * @var CommunicationConfigInterface
     */
    private $communicationConfig;

    /**
     * @var int
     */
    private $rpcConnectionTimeout;

    /**
     * @var PublisherConfig
     */
    private $publisherConfig;

    /**
     * @var ResponseQueueNameBuilder
     */
    private $responseQueueNameBuilder;

    /**
     * Initialize dependencies.
     *
     * @param Config $amqpConfig
     * @param PublisherConfig $publisherConfig
     * @param ResponseQueueNameBuilder $responseQueueNameBuilder
     * @param CommunicationConfigInterface $communicationConfig
     * @param int $rpcConnectionTimeout
     */
    public function __construct(
        Config $amqpConfig,
        PublisherConfig $publisherConfig,
        ResponseQueueNameBuilder $responseQueueNameBuilder,
        CommunicationConfigInterface $communicationConfig,
        $rpcConnectionTimeout = self::RPC_CONNECTION_TIMEOUT
    ) {
        $this->amqpConfig = $amqpConfig;
        $this->communicationConfig = $communicationConfig;
        $this->rpcConnectionTimeout = $rpcConnectionTimeout;
        $this->publisherConfig = $publisherConfig;
        $this->responseQueueNameBuilder = $responseQueueNameBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 103.0.0
     */
    public function enqueue($topic, EnvelopeInterface $envelope)
    {
        $topicData = $this->communicationConfig->getTopic($topic);
        $isSync = $topicData[CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS];

        $channel = $this->amqpConfig->getChannel();
        $exchange = $this->publisherConfig->getPublisher($topic)->getConnection()->getExchange();
        $responseBody = null;

        $msg = new AMQPMessage($envelope->getBody(), $envelope->getProperties());
        if ($isSync) {
            $correlationId = $envelope->getProperties()['correlation_id'];
            /** @var AMQPMessage $response */
            $callback = function ($response) use ($correlationId, &$responseBody, $channel) {
                if ($response->get('correlation_id') == $correlationId) {
                    $responseBody = $response->body;
                    $channel->basic_ack($response->get('delivery_tag'));
                } else {
                    //push message back to the queue
                    $channel->basic_reject($response->get('delivery_tag'), true);
                }
            };
            if ($envelope->getProperties()['reply_to']) {
                $replyTo = $envelope->getProperties()['reply_to'];
            } else {
                $replyTo = $this->responseQueueNameBuilder->getQueueName($topic);
            }
            $channel->basic_consume(
                $replyTo,
                '',
                false,
                false,
                false,
                false,
                $callback
            );
            $channel->basic_publish($msg, $exchange, $topic);
            while ($responseBody === null) {
                try {
                    $channel->wait(null, false, $this->rpcConnectionTimeout);
                } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                    throw new LocalizedException(
                        new \Magento\Framework\Phrase(
                            'The RPC (Remote Procedure Call) failed. The connection timed out after %time_out. '
                            . 'Please try again later.',
                            ['time_out' => $this->rpcConnectionTimeout]
                        )
                    );
                }
            }
        } else {
            $channel->basic_publish($msg, $exchange, $topic);
        }
        return $responseBody;
    }
}
