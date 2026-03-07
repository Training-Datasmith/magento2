<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\MessageQueue\CallbackInvokerInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\Registry;

/**
 * Class Consumer used to process OperationInterface messages.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassConsumer implements ConsumerInterface
{
    /**
     * @var MassConsumerEnvelopeCallbackFactory
     */
    private $massConsumerEnvelopeCallback;

    /**
     * Initialize dependencies.
     */
    public function __construct(
        private readonly CallbackInvokerInterface $invoker,
        private readonly ConsumerConfigurationInterface $configuration,
        MassConsumerEnvelopeCallbackFactory $massConsumerEnvelopeCallback,
        private readonly Registry $registry
    ) {
        $this->massConsumerEnvelopeCallback = $massConsumerEnvelopeCallback;
    }

    /**
     * @inheritdoc
     */
    public function process($maxNumberOfMessages = null): void
    {
        $this->registry->register('isSecureArea', true, true);

        $queue = $this->configuration->getQueue();
        $maxIdleTime = $this->configuration->getMaxIdleTime();
        $sleep = $this->configuration->getSleep();

        if (!isset($maxNumberOfMessages)) {
            $queue->subscribe($this->getTransactionCallback($queue));
        } else {
            $this->invoker->invoke(
                $queue,
                $maxNumberOfMessages,
                $this->getTransactionCallback($queue),
                $maxIdleTime,
                $sleep
            );
        }

        $this->registry->unregister('isSecureArea');
    }

    /**
     * Get transaction callback. This handles the case of async.
     *
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue)
    {
        $callbackInstance =  $this->massConsumerEnvelopeCallback->create(
            [
                'configuration' => $this->configuration,
                'queue' => $queue,
            ]
        );
        return function (EnvelopeInterface $message) use ($callbackInstance): void {
            $callbackInstance->execute($message);
        };
    }
}
