<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Application cache type list
 */
class Type_List implements Type_List_Interface
{
    public const INVALIDATED_TYPES = 'core_cache_invalidate';
    /**
     * @var \Magento\Framework\Cache\ConfigInterface
     */
    protected $_config;
    /**
     * @var InstanceFactory
     */
    protected $_factory;
    /**
     * @var StateInterface
     */
    protected $_cache_state;
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @param \Magento\Framework\Cache\ConfigInterface $config
     * @param StateInterface $cacheState
     * @param InstanceFactory $factory
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(\Magento\Framework\Cache\Config_Interface $config, State_Interface $cache_state, Instance_Factory $factory, \Magento\Framework\App\Cache_Interface $cache, ?Serializer_Interface $serializer = null)
    {
        $this->_config = $config;
        $this->_factory = $factory;
        $this->_cache_state = $cache_state;
        $this->_cache = $cache;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Serializer_Interface::class);
    }
    /**
     * Get cache class by cache type from configuration
     *
     * @param string $type
     * @return \Magento\Framework\Cache\FrontendInterface
     * @throws \UnexpectedValueException
     */
    protected function _get_type_instance($type)
    {
        $config = $this->_config->get_type($type);
        return $this->_factory->get($config['instance']);
    }
    /**
     * Get invalidate types codes
     *
     * @return array
     */
    protected function _get_invalidated_types()
    {
        $types = $this->_cache->load(self::INVALIDATED_TYPES);
        if ($types) {
            $types = $this->serializer->unserialize($types);
        } else {
            $types = [];
        }
        return $types;
    }
    /**
     * Save invalidated cache types
     *
     * @param array $types
     * @return void
     */
    protected function _save_invalidated_types($types)
    {
        $this->_cache->save($this->serializer->serialize($types), self::INVALIDATED_TYPES);
    }
    /**
     * Get information about all declared cache types
     *
     * @return array
     */
    public function get_types()
    {
        $types = [];
        $config = $this->_config->get_types();
        foreach ($config as $type => $node) {
            $type_instance = $this->_get_type_instance($type);
            if ($type_instance instanceof \Magento\Framework\Cache\Frontend\Decorator\Tag_Scope) {
                $type_tags = $type_instance->get_tag();
            } else {
                $type_tags = '';
            }
            $types[$type] = new \Magento\Framework\Data_Object(['id' => $type, 'cache_type' => $node['label'], 'description' => $node['description'], 'tags' => $type_tags, 'status' => (int) $this->_cache_state->is_enabled($type)]);
        }
        return $types;
    }
    /**
     * @inheritdoc
     */
    public function get_type_labels()
    {
        $types = [];
        foreach ($this->_config->get_types() as $type => $node) {
            if (array_key_exists('label', $node)) {
                $types[$type] = $node['label'];
            }
        }
        return $types;
    }
    /**
     * Get array of all invalidated cache types
     *
     * @return array
     */
    public function get_invalidated()
    {
        $invalidated_types = [];
        $types = $this->_get_invalidated_types();
        if ($types) {
            $all_types = $this->get_types();
            foreach (array_keys($types) as $type) {
                if (isset($all_types[$type]) && $this->_cache_state->is_enabled($type)) {
                    $invalidated_types[$type] = $all_types[$type];
                }
            }
        }
        return $invalidated_types;
    }
    /**
     * Mark specific cache type(s) as invalidated
     *
     * @param string|array $typeCode
     * @return void
     */
    public function invalidate($type_code)
    {
        $types = $this->_get_invalidated_types();
        if (!is_array($type_code)) {
            $type_code = [$type_code];
        }
        foreach ($type_code as $code) {
            $types[$code] = 1;
        }
        $this->_save_invalidated_types($types);
    }
    /**
     * Clean cached data for specific cache type
     *
     * @param string $typeCode
     * @return void
     */
    public function clean_type($type_code)
    {
        $this->_get_type_instance($type_code)->clean();
        $types = $this->_get_invalidated_types();
        unset($types[$type_code]);
        $this->_save_invalidated_types($types);
    }
}