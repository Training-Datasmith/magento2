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
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Cache_Set_Command extends Abstract_Cache_Manage_Command
{
    /**
     * Is enable cache or not
     *
     * @return bool
     */
    abstract protected function is_enable();
    /**
     * @inheritdoc
     */
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $is_enable = $this->is_enable();
        $types = $this->get_requested_types($input);
        $changed_types = $this->cache_manager->set_enabled($types, $is_enable);
        if ($changed_types) {
            $output->writeln('Changed cache status:');
            foreach ($changed_types as $type) {
                $output->writeln(sprintf('%30s: %d -> %d', $type, !$is_enable, $is_enable));
            }
        } else {
            $output->writeln('There is nothing to change in cache status');
        }
        if (!empty($changed_types) && $is_enable) {
            $this->cache_manager->clean($changed_types);
            $output->writeln('Cleaned cache types:');
            $output->writeln(join(PHP_EOL, $changed_types));
        }
        return Cli::RETURN_SUCCESS;
    }
}