<?php

declare (strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

/**
 * Command for disabling maintenance mode
 */
class Maintenance_Disable_Command extends Abstract_Maintenance_Command
{
    public const NAME = 'maintenance:disable';
    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->set_name(self::NAME)->set_description('Disables maintenance mode');
        parent::configure();
    }
    /**
     * Disable maintenance mode
     *
     * @return bool
     */
    protected function is_enable(): bool
    {
        return false;
    }
    /**
     * Get disabled maintenance mode display string
     *
     * @return string
     */
    protected function get_display_string(): string
    {
        return '<info>Disabled maintenance mode</info>';
    }
    /**
     * Return if IP addresses effective for maintenance mode were set
     *
     * @return bool
     */
    public function is_set_address_info(): bool
    {
        return count($this->maintenance_mode->get_address_info()) > 0;
    }
}