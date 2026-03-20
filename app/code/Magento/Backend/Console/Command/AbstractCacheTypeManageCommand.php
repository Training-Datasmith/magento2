<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Console\Cli;
use Magento\Framework\Event\Manager_Interface as EventManagerInterface;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Cache_Type_Manage_Command extends Abstract_Cache_Manage_Command
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $event_manager;
    /**
     * @param Manager $cacheManager
     * @param EventManagerInterface $eventManager
     */
    public function __construct(Manager $cache_manager, Event_Manager_Interface $event_manager)
    {
        $this->event_manager = $event_manager;
        parent::__construct($cache_manager);
    }
    /**
     * Perform a cache management action on cache types
     *
     * @param array $cacheTypes
     * @return void
     */
    abstract protected function perform_action(array $cache_types);
    /**
     * Get display message
     *
     * @return string
     */
    abstract protected function get_display_message();
    /**
     * Perform cache management action
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $types = $this->get_requested_types($input);
        $this->perform_action($types);
        $output->writeln($this->get_display_message());
        $output->writeln(join(PHP_EOL, $types));
        return Cli::RETURN_SUCCESS;
    }
}