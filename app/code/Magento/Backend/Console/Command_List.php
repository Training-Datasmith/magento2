<?php

declare (strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console;

use Magento\Backend\Console\Command\Maintenance_Allow_Ips_Command;
use Magento\Backend\Console\Command\Maintenance_Disable_Command;
use Magento\Backend\Console\Command\Maintenance_Enable_Command;
use Magento\Backend\Console\Command\Maintenance_Status_Command;
use Magento\Framework\Console\Command_List_Interface;
use Magento\Framework\Object_Manager_Interface;
/**
 * Provides list of commands to be available for uninstalled application
 */
class Command_List implements Command_List_Interface
{
    /**
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Gets list of command classes
     *
     * @return string[]
     */
    private function get_commands_classes(): array
    {
        return [Maintenance_Allow_Ips_Command::class, Maintenance_Disable_Command::class, Maintenance_Enable_Command::class, Maintenance_Status_Command::class];
    }
    /**
     * @inheritdoc
     */
    public function get_commands(): array
    {
        $commands = [];
        foreach ($this->get_commands_classes() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->object_manager->get($class);
            } else {
                throw new \RuntimeException('Class ' . $class . ' does not exist');
            }
        }
        return $commands;
    }
}