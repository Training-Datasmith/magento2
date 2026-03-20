<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp\Topology;

use Magento\Framework\Message_Queue\Topology\Config\Queue_Config_Item_Interface;
/**
 * Queue installer.
 */
class Queue_Installer
{
    use Argument_Processor;
    /**
     * Install queue.
     *
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param QueueConfigItemInterface $queue
     * @return void
     */
    public function install(\Php_Amqp_Lib\Channel\Amqp_Channel $channel, Queue_Config_Item_Interface $queue)
    {
        $channel->queue_declare($queue->get_name(), false, $queue->is_durable(), false, $queue->is_auto_delete(), false, $this->process_arguments($queue->get_arguments()));
    }
}