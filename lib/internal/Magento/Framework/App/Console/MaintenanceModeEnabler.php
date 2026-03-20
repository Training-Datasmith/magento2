<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Console;

use Magento\Framework\App\Maintenance_Mode;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Class MaintenanceModeEnabler
 * @package Magento\Framework\App\Console
 */
class Maintenance_Mode_Enabler
{
    /**
     * @var MaintenanceMode
     */
    private $maintenance_mode;
    /**
     * @var bool
     */
    private $skip_disable_maintenance_mode;
    /**
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(Maintenance_Mode $maintenance_mode)
    {
        $this->maintenance_mode = $maintenance_mode;
    }
    /**
     * Enable maintenance mode
     *
     * @param OutputInterface $output
     * @return void
     */
    private function enable_maintenance_mode(Output_Interface $output)
    {
        if ($this->maintenance_mode->is_on()) {
            $this->skip_disable_maintenance_mode = true;
            $output->writeln('<info>Maintenance mode already enabled</info>');
            return;
        }
        $this->maintenance_mode->set(true);
        $this->skip_disable_maintenance_mode = false;
        $output->writeln('<info>Enabling maintenance mode</info>');
    }
    /**
     * Disable maintenance mode
     *
     * @param OutputInterface $output
     * @return void
     */
    private function disable_maintenance_mode(Output_Interface $output)
    {
        if ($this->skip_disable_maintenance_mode) {
            $output->writeln('<info>Skipped disabling maintenance mode</info>');
            return;
        }
        $this->maintenance_mode->set(false);
        $output->writeln('<info>Disabling maintenance mode</info>');
    }
    /**
     * Run task in maintenance mode
     *
     * @param callable $task
     * @param OutputInterface $output
     * @param bool $holdMaintenanceOnFailure
     * @return mixed
     * @throws \Throwable if error occurred
     */
    public function execute_in_maintenance_mode(callable $task, Output_Interface $output, bool $hold_maintenance_on_failure)
    {
        $this->enable_maintenance_mode($output);
        try {
            $result = call_user_func($task);
        } catch (\Throwable $e) {
            if (!$hold_maintenance_on_failure) {
                $this->disable_maintenance_mode($output);
            }
            throw $e;
        }
        $this->disable_maintenance_mode($output);
        return $result;
    }
}