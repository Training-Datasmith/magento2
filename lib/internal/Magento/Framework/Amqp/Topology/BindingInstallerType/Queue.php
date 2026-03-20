<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp\Topology\Binding_Installer_Type;

use Magento\Framework\Amqp\Topology\Argument_Processor;
use Magento\Framework\Amqp\Topology\Binding_Installer_Interface;
use Magento\Framework\Message_Queue\Topology\Config\Exchange_Config_Item\Binding_Interface;
use Php_Amqp_Lib\Channel\Amqp_Channel;
/**
 * {@inheritdoc}
 */
class Queue implements Binding_Installer_Interface
{
    use Argument_Processor;
    /**
     * {@inheritdoc}
     */
    public function install(Amqp_Channel $channel, Binding_Interface $binding, $exchange_name)
    {
        $channel->queue_bind($binding->get_destination(), $exchange_name, $binding->get_topic(), false, $this->process_arguments($binding->get_arguments()));
    }
}