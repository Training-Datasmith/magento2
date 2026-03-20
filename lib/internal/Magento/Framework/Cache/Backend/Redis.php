<?php

declare (strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache\Backend;

/**
 * Redis wrapper to extend current implementation behaviour.
 */
class Redis extends \Cm_Cache_Backend_Redis
{
    /**
     * Local state of preloaded keys.
     *
     * @var array
     */
    private $preloaded_data = [];
    /**
     * Array of keys to be preloaded.
     *
     * @var array
     */
    private $preload_keys = [];
    /**
     * Whether to use lua on garbage collection
     *
     * @var bool
     */
    private bool $use_lua_on_gc;
    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->preload_keys = $options['preload_keys'] ?? [];
        parent::__construct($options);
        $this->use_lua_on_gc = isset($options['use_lua_on_gc']) ? (bool) $options['use_lua_on_gc'] : (bool) $this->_use_lua;
    }
    /**
     * Load value with given id from cache
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return bool|string
     */
    public function load($id, $do_not_test_cache_validity = false)
    {
        if (!empty($this->preload_keys) && empty($this->preloaded_data)) {
            $redis = $this->_slave ?? $this->_redis;
            $redis = $redis->pipeline();
            foreach ($this->preload_keys as $key) {
                $redis->h_get(self::PREFIX_KEY . $key, self::FIELD_DATA);
            }
            $redis_response = $redis->exec();
            $this->preloaded_data = is_array($redis_response) ? array_filter(array_combine($this->preload_keys, $redis_response)) : [];
        }
        if (isset($this->preloaded_data[$id])) {
            return $this->_decode_data($this->preloaded_data[$id]);
        }
        return parent::load($id, $do_not_test_cache_validity);
    }
    /**
     * Cover errors on save operations, which may occurs when Redis cannot evict keys, which is expected in some cases.
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param bool $specificLifetime
     * @return bool
     */
    public function save($data, $id, $tags = [], $specific_lifetime = 86400000)
    {
        // @todo add special handling of MAGE tag, save clenup
        try {
            $result = parent::save($data, $id, $tags, $specific_lifetime);
        } catch (\Throwable $exception) {
            $result = false;
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function remove($id)
    {
        try {
            $result = parent::remove($id);
        } catch (\Throwable $exception) {
            $result = false;
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    protected function _collect_garbage()
    {
        $use_lua = $this->_use_lua;
        $this->_use_lua = $this->use_lua_on_gc;
        try {
            parent::_collect_garbage();
        } finally {
            $this->_use_lua = $use_lua;
        }
    }
    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}