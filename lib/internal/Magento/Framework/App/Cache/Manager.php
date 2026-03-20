<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache;

/**
 * Cache status manager
 *
 * @api
 * @since 100.0.2
 */
class Manager
{
    /**
     * Cache types list
     *
     * @var TypeListInterface
     */
    private $cache_type_list;
    /**
     * Cache state service
     *
     * @var StateInterface
     */
    private $cache_state;
    /**
     * Cache types pool
     *
     * @var Type\FrontendPool
     */
    private $pool;
    /**
     * Constructor
     *
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface $cacheState
     * @param Type\FrontendPool $pool
     */
    public function __construct(Type_List_Interface $cache_type_list, State_Interface $cache_state, Type\Frontend_Pool $pool)
    {
        $this->cache_type_list = $cache_type_list;
        $this->cache_state = $cache_state;
        $this->pool = $pool;
    }
    /**
     * Updates cache status for the requested types
     *
     * @param string[] $types
     * @param bool $isEnabled
     * @return array List of types with changed status
     */
    public function set_enabled(array $types, $is_enabled)
    {
        $changed_status_types = [];
        $is_updated = false;
        foreach ($types as $type) {
            if ($this->cache_state->is_enabled($type) === $is_enabled) {
                // no need to poke it, if is not going to change
                continue;
            }
            $this->cache_state->set_enabled($type, $is_enabled);
            $is_updated = true;
            $changed_status_types[] = $type;
        }
        if ($is_updated) {
            $this->cache_state->persist();
        }
        return $changed_status_types;
    }
    /**
     * Cleans up caches
     *
     * @param array $types
     * @return void
     */
    public function clean(array $types)
    {
        foreach ($types as $type) {
            $this->cache_type_list->clean_type($type);
        }
    }
    /**
     * Flushes specified cache storages
     *
     * @param string[] $types
     * @return void
     */
    public function flush(array $types)
    {
        $flushed_backend = [];
        foreach ($types as $type) {
            $frontend = $this->pool->get($type);
            $backend = $frontend->get_backend();
            if (in_array($backend, $flushed_backend, true)) {
                // it was already flushed from another frontend
                continue;
            }
            // Call clean on frontend (not backend) for proper abstraction
            $frontend->clean();
            $flushed_backend[] = $backend;
        }
    }
    /**
     * Presents summary about cache status
     *
     * @return array
     */
    public function get_status()
    {
        $result = [];
        foreach ($this->cache_type_list->get_types() as $type) {
            $result[$type['id']] = $type['status'];
        }
        return $result;
    }
    /**
     * Get list of available cache types
     *
     * @return array
     */
    public function get_available_types()
    {
        $result = [];
        foreach ($this->cache_type_list->get_types() as $type) {
            $result[] = $type['id'];
        }
        return $result;
    }
}