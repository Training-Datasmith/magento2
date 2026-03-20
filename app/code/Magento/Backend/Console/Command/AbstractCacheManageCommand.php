<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
/**
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Cache_Manage_Command extends Abstract_Cache_Command
{
    /**
     * Input argument types
     */
    public const INPUT_KEY_TYPES = 'types';
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->add_argument(self::INPUT_KEY_TYPES, Input_Argument::IS_ARRAY, 'Space-separated list of cache types or omit to apply to all cache types.');
        parent::configure();
    }
    /**
     * Get requested cache types
     *
     * @param InputInterface $input
     * @return array
     */
    protected function get_requested_types(Input_Interface $input)
    {
        $requested_types = [];
        if ($input->get_argument(self::INPUT_KEY_TYPES)) {
            $requested_types = $input->get_argument(self::INPUT_KEY_TYPES);
            $requested_types = array_filter(array_map('trim', $requested_types), 'strlen');
        }
        if (empty($requested_types)) {
            return $this->cache_manager->get_available_types();
        } else {
            $available_types = $this->cache_manager->get_available_types();
            $unsupported_types = array_diff($requested_types, $available_types);
            if ($unsupported_types) {
                throw new \InvalidArgumentException("The following requested cache types are not supported: '" . join("', '", $unsupported_types) . "'." . PHP_EOL . 'Supported types: ' . join(', ', $available_types));
            }
            return array_values(array_intersect($available_types, $requested_types));
        }
    }
}