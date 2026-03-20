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
 * {@inheritdoc}
 */
class Binding_Installer implements Binding_Installer_Interface
{
    /**
     * @var BindingInstallerInterface[]
     */
    private $installers;
    /**
     * Initialize dependencies.
     *
     * @param BindingInstallerInterface[] $installers
     */
    public function __construct(array $installers)
    {
        $this->installers = $installers;
    }
    /**
     * {@inheritdoc}
     */
    public function install(Amqp_Channel $channel, Binding_Interface $binding, $exchange_name)
    {
        $this->get_installer($binding->get_destination_type())->install($channel, $binding, $exchange_name);
    }
    /**
     * Get binding installer by type.
     *
     * @param string $type
     * @return BindingInstallerInterface
     */
    private function get_installer($type)
    {
        if (!isset($this->installers[$type])) {
            throw new \InvalidArgumentException(sprintf('Installer type [%s] is not configured', $type));
        }
        return $this->installers[$type];
    }
}