<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp\Topology;

use Magento\Framework\Message_Queue\Topology\Config\Exchange_Config_Item_Interface;
/**
 * Exchange installer.
 */
class Exchange_Installer
{
    use Argument_Processor;
    /**
     * @var BindingInstallerInterface
     */
    private $binding_installer;
    /**
     * Initialize dependencies.
     *
     * @param BindingInstallerInterface $bindingInstaller
     */
    public function __construct(Binding_Installer_Interface $binding_installer)
    {
        $this->binding_installer = $binding_installer;
    }
    /**
     * Install exchange.
     *
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param ExchangeConfigItemInterface $exchange
     * @return void
     */
    public function install(\Php_Amqp_Lib\Channel\Amqp_Channel $channel, Exchange_Config_Item_Interface $exchange)
    {
        $channel->exchange_declare($exchange->get_name(), $exchange->get_type(), false, $exchange->is_durable(), $exchange->is_auto_delete(), $exchange->is_internal(), false, $this->process_arguments($exchange->get_arguments()));
        foreach ($exchange->get_bindings() as $binding) {
            $this->binding_installer->install($channel, $binding, $exchange->get_name());
        }
    }
}