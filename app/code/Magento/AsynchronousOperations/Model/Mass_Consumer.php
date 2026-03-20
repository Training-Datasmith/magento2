<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Framework\Message_Queue\Callback_Invoker_Interface;
use Magento\Framework\Message_Queue\Consumer_Configuration_Interface;
use Magento\Framework\Message_Queue\Consumer_Interface;
use Magento\Framework\Message_Queue\Envelope_Interface;
use Magento\Framework\Message_Queue\Queue_Interface;
use Magento\Framework\Registry;
/**
 * Class Consumer used to process OperationInterface messages.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mass_Consumer implements Consumer_Interface
{
    /**
     * @var MassConsumerEnvelopeCallbackFactory
     */
    private $mass_consumer_envelope_callback;
    /**
     * Initialize dependencies.
     */
    public function __construct(private readonly Callback_Invoker_Interface $invoker, private readonly Consumer_Configuration_Interface $configuration, Mass_Consumer_Envelope_Callback_Factory $mass_consumer_envelope_callback, private readonly Registry $registry)
    {
        $this->mass_consumer_envelope_callback = $mass_consumer_envelope_callback;
    }
    /**
     * @inheritdoc
     */
    public function process($max_number_of_messages = null): void
    {
        $this->registry->register('isSecureArea', true, true);
        $queue = $this->configuration->get_queue();
        $max_idle_time = $this->configuration->get_max_idle_time();
        $sleep = $this->configuration->get_sleep();
        if (!isset($max_number_of_messages)) {
            $queue->subscribe($this->get_transaction_callback($queue));
        } else {
            $this->invoker->invoke($queue, $max_number_of_messages, $this->get_transaction_callback($queue), $max_idle_time, $sleep);
        }
        $this->registry->unregister('isSecureArea');
    }
    /**
     * Get transaction callback. This handles the case of async.
     *
     * @return \Closure
     */
    private function get_transaction_callback(Queue_Interface $queue)
    {
        $callback_instance = $this->mass_consumer_envelope_callback->create(['configuration' => $this->configuration, 'queue' => $queue]);
        return function (Envelope_Interface $message) use ($callback_instance): void {
            $callback_instance->execute($message);
        };
    }
}