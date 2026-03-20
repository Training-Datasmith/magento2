<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Asynchronous_Operations\Model;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Exception\Not_Found_Exception;
use Magento\Framework\Message_Queue\Connection_Lost_Exception;
use Magento\Framework\Message_Queue\Consumer_Configuration_Interface;
use Magento\Framework\Message_Queue\Envelope_Interface;
use Magento\Framework\Message_Queue\Lock_Interface;
use Magento\Framework\Message_Queue\Message_Lock_Exception;
use Magento\Framework\Message_Queue\Queue_Interface;
use Psr\Log\Logger_Interface;
/**
 * Class used as public callback function by async consumer.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mass_Consumer_Envelope_Callback
{
    /**
     * @var OperationProcessor
     */
    private $operation_processor;
    /**
     * @var MessageControllerDecorator
     */
    private $message_controller_decorator;
    public function __construct(private readonly Resource_Connection $resource, private readonly Consumer_Configuration_Interface $configuration, Operation_Processor_Factory $operation_processor_factory, private readonly Logger_Interface $logger, private readonly Queue_Interface $queue, ?Message_Controller_Decorator $message_controller_decorator = null)
    {
        $this->operation_processor = $operation_processor_factory->create(['configuration' => $this->configuration]);
        $this->message_controller_decorator = $message_controller_decorator ?: Object_Manager::get_instance()->get(Message_Controller_Decorator::class);
    }
    /**
     * Get transaction callback. This handles the case of async.
     */
    public function execute(Envelope_Interface $message): void
    {
        $queue = $this->queue;
        /** @var LockInterface $lock */
        $lock = null;
        try {
            $topic_name = $message->get_properties()['topic_name'];
            $lock = $this->message_controller_decorator->lock($message, $this->configuration->get_consumer_name());
            $allowed_topics = $this->configuration->get_topic_names();
            if (in_array($topic_name, $allowed_topics)) {
                $this->operation_processor->process($message->get_body());
            } else {
                $queue->reject($message);
                return;
            }
            $queue->acknowledge($message);
        } catch (Message_Lock_Exception) {
            $queue->acknowledge($message);
        } catch (Connection_Lost_Exception) {
            if ($lock) {
                $this->resource->get_connection()->delete($this->resource->get_table_name('queue_lock'), ['id = ?' => $lock->get_id()]);
            }
        } catch (Not_Found_Exception $e) {
            $queue->acknowledge($message);
            $this->logger->warning($e->get_message());
        } catch (\Exception $e) {
            $queue->reject($message, false, $e->get_message());
            if ($lock) {
                $this->resource->get_connection()->delete($this->resource->get_table_name('queue_lock'), ['id = ?' => $lock->get_id()]);
            }
        }
    }
    /**
     * Get message queue.
     *
     * @return QueueInterface
     */
    public function get_queue()
    {
        return $this->queue;
    }
}