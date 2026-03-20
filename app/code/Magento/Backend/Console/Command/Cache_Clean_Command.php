<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

/**
 * Command for cleaning cache
 *
 * @api
 * @since 100.0.2
 */
class Cache_Clean_Command extends Abstract_Cache_Type_Manage_Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->set_name('cache:clean');
        $this->set_description('Cleans cache type(s)');
        parent::configure();
    }
    /**
     * Cleans cache types
     *
     * @param array $cacheTypes
     * @return void
     */
    protected function perform_action(array $cache_types)
    {
        if ($cache_types === [] || in_array('full_page', $cache_types)) {
            $this->event_manager->dispatch('adminhtml_cache_flush_system');
        }
        $this->cache_manager->clean($cache_types);
    }
    /**
     * @inheritdoc
     */
    protected function get_display_message()
    {
        return 'Cleaned cache types:';
    }
}