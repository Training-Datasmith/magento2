<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache;

/**
 * Notifier for stale cache retrieval detection
 *
 * @api
 */
interface Stale_Cache_Notifier_Interface
{
    /**
     * Notifies of stale cache being used by any cache loader
     */
    public function cache_loader_is_using_stale_cache();
}