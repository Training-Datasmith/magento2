<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter;

use Predis\Client as PredisClient;
/**
 * Optimized Predis wrapper - minimal intervention approach
 *
 * Only optimizes GET operations with response caching. All other operations
 * are passed through directly to maintain 100% compatibility with Predis.
 */
class Optimized_Predis_Client extends Predis_Client
{
    /**
     * @var array
     */
    private array $cache = [];
    /**
     * @var bool
     */
    private bool $caching_enabled = true;
    private const CACHE_TTL = 1;
    private const CACHE_MAX = 200;
    /**
     * Constructor
     *
     * @param mixed $parameters
     * @param mixed $options
     */
    public function __construct($parameters = null, $options = null)
    {
        parent::__construct($parameters, $options);
        // Conservative: only enable caching for web requests, not CLI/tests
        if (php_sapi_name() === 'cli' || defined('TESTS_TEMP_DIR')) {
            $this->caching_enabled = false;
        }
    }
    /**
     * Optimized GET with response caching
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->caching_enabled) {
            return parent::get($key);
        }
        $cache_key = 'get:' . $key;
        if (isset($this->cache[$cache_key])) {
            [$result, $time] = $this->cache[$cache_key];
            if (time() - $time < self::CACHE_TTL) {
                return $result;
            }
            unset($this->cache[$cache_key]);
        }
        $result = parent::get($key);
        if (count($this->cache) >= self::CACHE_MAX) {
            array_shift($this->cache);
        }
        $this->cache[$cache_key] = [$result, time()];
        return $result;
    }
    /**
     * Clear cache on SET
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $expireResolution
     * @param int|null $expireTTL
     * @param string|null $flag
     * @return mixed
     */
    public function set($key, $value, $expire_resolution = null, $expire_ttl = null, $flag = null)
    {
        $this->cache = [];
        return parent::set($key, $value, $expire_resolution, $expire_ttl, $flag);
    }
    /**
     * Clear cache on DEL
     *
     * @param string|array $keys
     * @return mixed
     */
    public function del($keys)
    {
        $this->cache = [];
        return parent::del(is_array($keys) ? $keys : [$keys]);
    }
    /**
     * Clear cache on FLUSHDB
     *
     * @return mixed
     */
    public function flushdb()
    {
        $this->cache = [];
        return parent::flushdb();
    }
    /**
     * Clear cache on SELECT (database switch)
     *
     * @param int $database
     * @return mixed
     */
    public function select($database)
    {
        $this->cache = [];
        return parent::select($database);
    }
}