<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Represents loaded and cached configuration data, should be used to gain access to different types
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @api
 * @since 100.0.2
 */
class Data implements \Magento\Framework\Config\Data_Interface
{
    /**
     * Configuration reader
     *
     * @var ReaderInterface
     */
    protected $_reader;
    /**
     * Configuration cache
     *
     * @var CacheInterface
     */
    protected $_cache;
    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cache_id;
    /**
     * @var array
     */
    protected $cache_tags = [];
    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];
    /**
     * @var ReaderInterface
     */
    private $reader;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var string
     */
    private $cache_id;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * Constructor
     *
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     * @param array|null $cacheTags
     */
    public function __construct(Reader_Interface $reader, Cache_Interface $cache, $cache_id, ?Serializer_Interface $serializer = null, ?array $cache_tags = null)
    {
        $this->reader = $reader;
        $this->cache = $cache;
        $this->cache_id = $cache_id;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Serializer_Interface::class);
        if ($cache_tags) {
            $this->cache_tags = $cache_tags;
        }
        $this->init_data();
    }
    /**
     * Initialise data for configuration
     *
     * @return void
     */
    protected function init_data()
    {
        $data = $this->cache->load($this->cache_id);
        if (false === $data) {
            $data = $this->reader->read();
            $this->cache->save($this->serializer->serialize($data), $this->cache_id, $this->cache_tags);
        } else {
            $data = $this->serializer->unserialize($data);
        }
        $this->merge($data);
    }
    /**
     * Merge config data to the object
     *
     * @param array $config
     * @return void
     */
    public function merge(array $config)
    {
        $this->_data = array_replace_recursive($this->_data, $config);
    }
    /**
     * Get config value by key
     *
     * @param string $path
     * @param mixed $default
     * @return array|mixed|null
     */
    public function get($path = null, $default = null)
    {
        if ($path === null) {
            return $this->_data;
        }
        $keys = explode('/', $path);
        $data = $this->_data;
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }
        return $data;
    }
    /**
     * Clear cache data
     *
     * @return void
     */
    public function reset()
    {
        $this->cache->remove($this->cache_id);
        $this->_data = [];
        $config_data = $this->reader->read();
        if ($config_data) {
            $this->merge($config_data);
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