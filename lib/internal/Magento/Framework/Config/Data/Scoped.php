<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config\Data;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Provides scoped configuration
 * @api
 * @since 100.0.2
 */
class Scoped extends \Magento\Framework\Config\Data
{
    /**
     * Configuration scope resolver
     *
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_config_scope;
    /**
     * Scope priority loading scheme
     *
     * @var string[]
     */
    protected $_scope_priority_scheme = [];
    /**
     * @var array
     */
    protected $_loaded_scopes = [];
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\ReaderInterface $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(\Magento\Framework\Config\Reader_Interface $reader, \Magento\Framework\Config\Scope_Interface $config_scope, \Magento\Framework\Config\Cache_Interface $cache, $cache_id, ?Serializer_Interface $serializer = null)
    {
        $this->_reader = $reader;
        $this->_config_scope = $config_scope;
        $this->_cache = $cache;
        $this->_cache_id = $cache_id;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Serializer_Interface::class);
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
        $this->_load_scoped_data();
        return parent::get($path, $default);
    }
    /**
     * Load data for current scope
     *
     * @return void
     */
    protected function _load_scoped_data()
    {
        $scope = $this->_config_scope->get_current_scope() ?? '';
        if (false == isset($this->_loaded_scopes[$scope])) {
            if (false == in_array($scope, $this->_scope_priority_scheme)) {
                $this->_scope_priority_scheme[] = $scope;
            }
            foreach ($this->_scope_priority_scheme as $scope_code) {
                if (false == isset($this->_loaded_scopes[$scope_code])) {
                    if ($scope_code !== 'primary' && $data = $this->_cache->load($scope_code . '::' . $this->_cache_id)) {
                        $data = $this->serializer->unserialize($data);
                    } else {
                        $data = $this->_reader->read($scope_code);
                        if ($scope_code !== 'primary') {
                            $this->_cache->save($this->serializer->serialize($data), $scope_code . '::' . $this->_cache_id);
                        }
                    }
                    $this->merge($data);
                    $this->_loaded_scopes[$scope_code] = true;
                }
                if ($scope_code == $scope) {
                    break;
                }
            }
        }
    }
}