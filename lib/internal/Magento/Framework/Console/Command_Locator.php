<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Console;

/**
 * Locator for Console commands
 */
class Command_Locator
{
    /**
     * @var string[]
     */
    private static $commands = [];
    /**
     * @param string $commandListClass
     * @return void
     */
    public static function register($command_list_class)
    {
        self::$commands[] = $command_list_class;
    }
    /**
     * @return string[]
     */
    public static function get_commands()
    {
        return self::$commands;
    }
}