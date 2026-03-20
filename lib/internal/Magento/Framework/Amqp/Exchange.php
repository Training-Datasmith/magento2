<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Communication\Config_Interface as CommunicationConfigInterface;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Message_Queue\Envelope_Interface;
use Magento\Framework\Message_Queue\Exchange_Interface;
use Magento\Framework\Message_Queue\Publisher\Config_Interface as PublisherConfig;
use Magento\Framework\Message_Queue\Rpc\Response_Queue_Name_Builder;
use Php_Amqp_Lib\Message\Amqp_Message;
/**
 * Class message exchange.
 *
 * @api
 * @since 103.0.0
 */
class Exchange implements Exchange_Interface
{
    public const RPC_CONNECTION_TIMEOUT = 30;
    /**
     * @var Config
     */
    private $amqp_config;
    /**
     * @var CommunicationConfigInterface
     */
    private $communication_config;
    /**
     * @var int
     */
    private $rpc_connection_timeout;
    /**
     * @var PublisherConfig
     */
    private $publisher_config;
    /**
     * @var ResponseQueueNameBuilder
     */
    private $response_queue_name_builder;
    /**
     * Initialize dependencies.
     *
     * @param Config $amqpConfig
     * @param PublisherConfig $publisherConfig
     * @param ResponseQueueNameBuilder $responseQueueNameBuilder
     * @param CommunicationConfigInterface $communicationConfig
     * @param int $rpcConnectionTimeout
     */
    public function __construct(Config $amqp_config, Publisher_Config $publisher_config, Response_Queue_Name_Builder $response_queue_name_builder, Communication_Config_Interface $communication_config, $rpc_connection_timeout = self::RPC_CONNECTION_TIMEOUT)
    {
        $this->amqp_config = $amqp_config;
        $this->communication_config = $communication_config;
        $this->rpc_connection_timeout = $rpc_connection_timeout;
        $this->publisher_config = $publisher_config;
        $this->response_queue_name_builder = $response_queue_name_builder;
    }
    /**
     * {@inheritdoc}
     * @since 103.0.0
     */
    public function enqueue($topic, Envelope_Interface $envelope)
    {
        $topic_data = $this->communication_config->get_topic($topic);
        $is_sync = $topic_data[Communication_Config_Interface::TOPIC_IS_SYNCHRONOUS];
        $channel = $this->amqp_config->get_channel();
        $exchange = $this->publisher_config->get_publisher($topic)->get_connection()->get_exchange();
        $response_body = null;
        $msg = new Amqp_Message($envelope->get_body(), $envelope->get_properties());
        if ($is_sync) {
            $correlation_id = $envelope->get_properties()['correlation_id'];
            /** @var AMQPMessage $response */
            $callback = function ($response) use ($correlation_id, &$response_body, $channel) {
                if ($response->get('correlation_id') == $correlation_id) {
                    $response_body = $response->body;
                    $channel->basic_ack($response->get('delivery_tag'));
                } else {
                    //push message back to the queue
                    $channel->basic_reject($response->get('delivery_tag'), true);
                }
            };
            if ($envelope->get_properties()['reply_to']) {
                $reply_to = $envelope->get_properties()['reply_to'];
            } else {
                $reply_to = $this->response_queue_name_builder->get_queue_name($topic);
            }
            $channel->basic_consume($reply_to, '', false, false, false, false, $callback);
            $channel->basic_publish($msg, $exchange, $topic);
            while ($response_body === null) {
                try {
                    $channel->wait(null, false, $this->rpc_connection_timeout);
                } catch (\Php_Amqp_Lib\Exception\Amqp_Timeout_Exception $e) {
                    throw new Localized_Exception(new \Magento\Framework\Phrase('The RPC (Remote Procedure Call) failed. The connection timed out after %time_out. ' . 'Please try again later.', ['time_out' => $this->rpc_connection_timeout]));
                }
            }
        } else {
            $channel->basic_publish($msg, $exchange, $topic);
        }
        return $response_body;
    }
}