<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Command for checking cache status
 *
 * @api
 * @since 100.0.2
 */
class Cache_Status_Command extends Abstract_Cache_Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->set_name('cache:status');
        $this->set_description('Checks cache status');
        parent::configure();
    }
    /**
     * @inheritdoc
     */
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $output->writeln('Current status:');
        foreach ($this->cache_manager->get_status() as $cache => $status) {
            $output->writeln(sprintf('%30s: %d', $cache, $status));
        }
        return Cli::RETURN_SUCCESS;
    }
}