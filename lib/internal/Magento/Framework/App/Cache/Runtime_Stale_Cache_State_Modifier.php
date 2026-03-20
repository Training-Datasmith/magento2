<?php

declare (strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache;

use Magento\Framework\Cache\Stale_Cache_Notifier_Interface;
/**
 * Modifier of runtime cache state based on stale data notification from cache loader
 */
class Runtime_Stale_Cache_State_Modifier implements Stale_Cache_Notifier_Interface
{
    /** @var StateInterface */
    private $cache_state;
    /** @var string[] */
    private $cache_types;
    /**
     * @param StateInterface $cacheState
     * @param string[] $cacheTypes
     */
    public function __construct(State_Interface $cache_state, array $cache_types = [])
    {
        $this->cache_state = $cache_state;
        $this->cache_types = $cache_types;
    }
    /**
     * Disabled configures cache types when stale cache was detected in the current request
     */
    public function cache_loader_is_using_stale_cache()
    {
        foreach ($this->cache_types as $type) {
            $this->cache_state->set_enabled($type, false);
        }
    }
}