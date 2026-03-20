<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache;

/**
 * @api
 * @since 100.0.2
 */
interface State_Interface
{
    /**
     * Whether a cache type is enabled at the moment or not
     *
     * @param string $cacheType
     * @return bool
     */
    public function is_enabled($cache_type);
    /**
     * Enable/disable a cache type in run-time
     *
     * @param string $cacheType
     * @param bool $isEnabled
     * @return void
     */
    public function set_enabled($cache_type, $is_enabled);
    /**
     * Save the current statuses (enabled/disabled) of cache types to the persistent storage
     *
     * @return void
     */
    public function persist();
}