<?php

declare (strict_types=1);
/**
 * Object manager configuration cache
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Object_Manager;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Serializer_Interface;
class Config_Cache implements \Magento\Framework\Object_Manager\Config_Cache_Interface
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache_frontend;
    /**
     * Cache prefix
     *
     * @var string
     */
    protected $_prefix = 'diConfig';
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @param \Magento\Framework\Cache\FrontendInterface $cacheFrontend
     */
    public function __construct(\Magento\Framework\Cache\Frontend_Interface $cache_frontend)
    {
        $this->_cache_frontend = $cache_frontend;
    }
    /**
     * Retrieve configuration from cache
     *
     * @param string $key
     * @return array|false
     */
    public function get($key)
    {
        $data = $this->_cache_frontend->load($this->_prefix . $key);
        if (!$data) {
            return false;
        }
        return $this->get_serializer()->unserialize($data);
    }
    /**
     * Save config to cache
     *
     * @param array $config
     * @param string $key
     * @return void
     */
    public function save(array $config, $key)
    {
        $this->_cache_frontend->save($this->get_serializer()->serialize($config), $this->_prefix . $key);
    }
    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated 101.0.0
     */
    private function get_serializer()
    {
        if (null === $this->serializer) {
            $this->serializer = \Magento\Framework\App\Object_Manager::get_instance()->get(Serialize::class);
        }
        return $this->serializer;
    }
}