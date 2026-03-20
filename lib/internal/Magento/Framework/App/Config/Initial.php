<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Initial configuration data container. Provides interface for reading initial config values
 */
class Initial
{
    /**
     * Cache identifier used to store initial config
     */
    public const CACHE_ID = 'initial_config';
    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];
    /**
     * Config metadata
     *
     * @var array
     */
    protected $_metadata = [];
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * Initial constructor
     *
     * @param Initial\Reader $reader
     * @param \Magento\Framework\App\Cache\Type\Config $cache
     * @param SerializerInterface|null $serializer
     */
    public function __construct(\Magento\Framework\App\Config\Initial\Reader $reader, \Magento\Framework\App\Cache\Type\Config $cache, ?Serializer_Interface $serializer = null)
    {
        $this->serializer = $serializer ?: \Magento\Framework\App\Object_Manager::get_instance()->get(Serializer_Interface::class);
        $data = $cache->load(self::CACHE_ID);
        if (!$data) {
            $data = $reader->read();
            $cache->save($this->serializer->serialize($data), self::CACHE_ID);
        } else {
            $data = $this->serializer->unserialize($data);
        }
        $this->_data = $data['data'];
        $this->_metadata = $data['metadata'];
    }
    /**
     * Get initial data by given scope
     *
     * @param string $scope Format is scope type and scope code separated by pipe: e.g. "type|code"
     * @return array
     */
    public function get_data($scope)
    {
        [$scope_type, $scope_code] = array_pad(explode('|', (string) $scope), 2, null);
        if (Scope_Config_Interface::SCOPE_TYPE_DEFAULT == $scope_type) {
            return $this->_data[$scope_type] ?? [];
        } elseif ($scope_code) {
            return $this->_data[$scope_type][$scope_code] ?? [];
        }
        return [];
    }
    /**
     * Get configuration metadata
     *
     * @return array
     */
    public function get_metadata()
    {
        return $this->_metadata;
    }
}