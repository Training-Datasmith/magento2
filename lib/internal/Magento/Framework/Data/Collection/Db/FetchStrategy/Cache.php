<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Collection\Db\Fetch_Strategy;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Retrieve collection data from cache, fail over to another fetch strategy, if cache does not exist yet
 */
class Cache implements \Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_cache;
    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     */
    private $_fetch_strategy;
    /**
     * @var string
     */
    protected $_cache_id_prefix = '';
    /**
     * @var array
     */
    protected $_cache_tags = [];
    /**
     * @var int|bool|null
     */
    protected $_cache_lifetime = null;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * Constructor
     *
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param string $cacheIdPrefix
     * @param array $cacheTags
     * @param int|bool|null $cacheLifetime
     * @param SerializerInterface|null $serializer
     */
    public function __construct(\Magento\Framework\Cache\Frontend_Interface $cache, \Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface $fetch_strategy, $cache_id_prefix = '', array $cache_tags = [], $cache_lifetime = null, ?Serializer_Interface $serializer = null)
    {
        $this->_cache = $cache;
        $this->_fetch_strategy = $fetch_strategy;
        $this->_cache_id_prefix = $cache_id_prefix;
        $this->_cache_tags = $cache_tags;
        $this->_cache_lifetime = $cache_lifetime;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Serializer_Interface::class);
    }
    /**
     * {@inheritdoc}
     */
    public function fetch_all(Select $select, array $bind_params = [])
    {
        $cache_id = $this->_get_select_cache_id($select);
        $result = $this->_cache->load($cache_id);
        if ($result) {
            $result = $this->serializer->unserialize($result);
        } else {
            $result = $this->_fetch_strategy->fetch_all($select, $bind_params);
            $this->_cache->save($this->serializer->serialize($result), $cache_id, $this->_cache_tags, $this->_cache_lifetime);
        }
        return $result;
    }
    /**
     * Determine cache identifier based on select query
     *
     * @param \Magento\Framework\DB\Select|string $select
     * @return string
     */
    protected function _get_select_cache_id($select)
    {
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        return $this->_cache_id_prefix . md5((string) $select);
    }
}