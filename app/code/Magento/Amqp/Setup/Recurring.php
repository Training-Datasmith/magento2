<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Setup;

use Magento\Framework\Setup\Install_Schema_Interface;
use Magento\Framework\Setup\Module_Context_Interface;
use Magento\Framework\Setup\Schema_Setup_Interface;
class Recurring implements Install_Schema_Interface
{
    public function __construct(protected \Magento\Framework\Amqp\Topology_Installer $topology_installer)
    {
    }
    /**
     * @inheritdoc
     */
    public function install(Schema_Setup_Interface $setup, Module_Context_Interface $context): void
    {
        $this->topology_installer->install();
    }
}