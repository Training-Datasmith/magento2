<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

/**
 * Command for flushing cache
 *
 * @api
 * @since 100.0.2
 */
class Cache_Flush_Command extends Abstract_Cache_Type_Manage_Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->set_name('cache:flush');
        $this->set_description('Flushes cache storage used by cache type(s)');
        parent::configure();
    }
    /**
     * Flushes cache types
     *
     * @param array $cacheTypes
     * @return void
     */
    protected function perform_action(array $cache_types)
    {
        if ($cache_types === [] || in_array('full_page', $cache_types)) {
            $this->event_manager->dispatch('adminhtml_cache_flush_all');
        }
        $this->cache_manager->flush($cache_types);
    }
    /**
     * @inheritdoc
     */
    protected function get_display_message()
    {
        return 'Flushed cache types:';
    }
}