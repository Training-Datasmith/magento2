<?php

declare (strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

/**
 * Command for enabling maintenance mode
 */
class Maintenance_Enable_Command extends Abstract_Maintenance_Command
{
    public const NAME = 'maintenance:enable';
    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->set_name(self::NAME)->set_description('Enables maintenance mode');
        parent::configure();
    }
    /**
     * Enable maintenance mode
     *
     * @return bool
     */
    protected function is_enable(): bool
    {
        return true;
    }
    /**
     * Get enabled maintenance mode display string
     *
     * @return string
     */
    protected function get_display_string(): string
    {
        return '<info>Enabled maintenance mode</info>';
    }
}