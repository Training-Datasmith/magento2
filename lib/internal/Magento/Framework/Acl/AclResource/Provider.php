<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\Acl_Resource;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Serialize\Serializer\Json;
class Provider implements Provider_Interface
{
    /**
     * Cache key for ACL roles cache
     */
    public const ACL_RESOURCES_CACHE_KEY = 'provider_acl_resources_cache';
    /**
     * @var \Magento\Framework\Config\ReaderInterface
     */
    protected $_config_reader;
    /**
     * @var TreeBuilder
     */
    protected $_resource_tree_builder;
    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface
     */
    private $acl_data_cache;
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var string
     */
    private $cache_key;
    /**
     * @param \Magento\Framework\Config\ReaderInterface $configReader
     * @param TreeBuilder $resourceTreeBuilder
     * @param \Magento\Framework\Acl\Data\CacheInterface $aclDataCache
     * @param Json $serializer
     * @param string $cacheKey
     */
    public function __construct(\Magento\Framework\Config\Reader_Interface $config_reader, Tree_Builder $resource_tree_builder, ?\Magento\Framework\Acl\Data\Cache_Interface $acl_data_cache = null, ?Json $serializer = null, $cache_key = self::ACL_RESOURCES_CACHE_KEY)
    {
        $this->_config_reader = $config_reader;
        $this->_resource_tree_builder = $resource_tree_builder;
        $this->acl_data_cache = $acl_data_cache ?: Object_Manager::get_instance()->get(\Magento\Framework\Config\Cache_Interface::class);
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Json::class);
        $this->cache_key = $cache_key;
    }
    /**
     * {@inheritdoc}
     */
    public function get_acl_resources()
    {
        $tree = $this->acl_data_cache->load($this->cache_key);
        if ($tree) {
            return $this->serializer->unserialize($tree);
        }
        $acl_resource_config = $this->_config_reader->read();
        if (!empty($acl_resource_config['config']['acl']['resources'])) {
            $tree = $this->_resource_tree_builder->build($acl_resource_config['config']['acl']['resources']);
            $this->acl_data_cache->save($this->serializer->serialize($tree), $this->cache_key);
            return $tree;
        }
        return [];
    }
}