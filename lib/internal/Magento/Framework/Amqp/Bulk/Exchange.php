<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp\Bulk;

use Magento\Framework\Communication\Config_Interface as CommunicationConfigInterface;
use Magento\Framework\Message_Queue\Bulk\Exchange_Interface;
use Magento\Framework\Message_Queue\Publisher\Config_Interface as PublisherConfig;
use Php_Amqp_Lib\Message\Amqp_Message;
/**
 * Used to send messages in bulk in AMQP queue.
 */
class Exchange implements Exchange_Interface
{
    /**
     * @var \Magento\Framework\Amqp\Config
     */
    private $amqp_config;
    /**
     * @var CommunicationConfigInterface
     */
    private $communication_config;
    /**
     * @var PublisherConfig
     */
    private $publisher_config;
    /**
     * @var \Magento\Framework\Amqp\Exchange
     */
    private $exchange;
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Amqp\Config $amqpConfig
     * @param PublisherConfig $publisherConfig
     * @param CommunicationConfigInterface $communicationConfig
     * @param \Magento\Framework\Amqp\Exchange $exchange
     */
    public function __construct(\Magento\Framework\Amqp\Config $amqp_config, Publisher_Config $publisher_config, Communication_Config_Interface $communication_config, \Magento\Framework\Amqp\Exchange $exchange)
    {
        $this->amqp_config = $amqp_config;
        $this->communication_config = $communication_config;
        $this->publisher_config = $publisher_config;
        $this->exchange = $exchange;
    }
    /**
     * @inheritdoc
     */
    public function enqueue($topic, array $envelopes)
    {
        $topic_data = $this->communication_config->get_topic($topic);
        $is_sync = $topic_data[Communication_Config_Interface::TOPIC_IS_SYNCHRONOUS];
        if ($is_sync) {
            $responses = [];
            foreach ($envelopes as $envelope) {
                $responses[] = $this->exchange->enqueue($topic, $envelope);
            }
            return $responses;
        }
        $channel = $this->amqp_config->get_channel();
        $publisher = $this->publisher_config->get_publisher($topic);
        $exchange = $publisher->get_connection()->get_exchange();
        foreach ($envelopes as $envelope) {
            // @codingStandardsIgnoreStart
            $msg = new Amqp_Message($envelope->get_body(), array_merge(['delivery_mode' => 2], $envelope->get_properties()));
            // @codingStandardsIgnoreEnd
            $channel->batch_basic_publish($msg, $exchange, $topic);
        }
        $channel->publish_batch();
        return null;
    }
}