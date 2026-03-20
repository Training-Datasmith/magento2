<?php

declare (strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

use Magento\Framework\App\Maintenance_Mode;
use Magento\Framework\Console\Cli;
use Magento\Setup\Console\Command\Abstract_Setup_Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Command for checking maintenance mode status
 */
class Maintenance_Status_Command extends Abstract_Setup_Command
{
    public const NAME = 'maintenance:status';
    /**
     * @var MaintenanceMode $maintenanceMode
     */
    private $maintenance_mode;
    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(Maintenance_Mode $maintenance_mode)
    {
        $this->maintenance_mode = $maintenance_mode;
        parent::__construct();
    }
    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->set_name(self::NAME)->set_description('Displays maintenance mode status');
        parent::configure();
    }
    /**
     * @inheritDoc
     */
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $output->writeln('<info>Status: maintenance mode is ' . ($this->maintenance_mode->is_on() ? 'enabled' : 'disabled') . '</info>');
        $address_info = $this->maintenance_mode->get_address_info();
        $addresses = implode(' ', $address_info);
        $output->writeln('<info>List of exempt IP-addresses: ' . ($addresses ? $addresses : 'none') . '</info>');
        return Cli::RETURN_SUCCESS;
    }
}