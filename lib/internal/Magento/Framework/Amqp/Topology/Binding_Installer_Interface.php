<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp\Topology;

use Magento\Framework\Message_Queue\Topology\Config\Exchange_Config_Item\Binding_Interface;
use Php_Amqp_Lib\Channel\Amqp_Channel;
/**
 * Exchange binding installer.
 *
 * @api
 */
interface Binding_Installer_Interface
{
    /**
     * Install exchange bindings.
     *
     * @param AMQPChannel $channel
     * @param BindingInterface $binding
     * @param string $exchangeName
     * @return void
     */
    public function install(Amqp_Channel $channel, Binding_Interface $binding, $exchange_name);
}