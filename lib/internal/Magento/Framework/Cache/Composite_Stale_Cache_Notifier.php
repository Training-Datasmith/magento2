<?php

declare (strict_types=1);
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache;

/**
 * Composite stale cache notifier
 *
 * Introduces an extension point to be used by other modules for disabling
 * own cache write when stale cache load detected
 */
class Composite_Stale_Cache_Notifier implements Stale_Cache_Notifier_Interface
{
    /**
     * @var StaleCacheNotifierInterface[]
     */
    private $notifiers = [];
    /**
     * CompositeStaleCacheNotifier constructor.
     * @param StaleCacheNotifierInterface[] $notifiers
     */
    public function __construct(array $notifiers = [])
    {
        $this->notifiers = $notifiers;
    }
    /**
     * Notifies every added cache notifier of stale cache
     */
    public function cache_loader_is_using_stale_cache()
    {
        foreach ($this->notifiers as $notifier) {
            $notifier->cache_loader_is_using_stale_cache();
        }
    }
}