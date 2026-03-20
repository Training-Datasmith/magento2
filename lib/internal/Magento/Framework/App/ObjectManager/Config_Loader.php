<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Object_Manager;

use Magento\Framework\Object_Manager\Config_Loader_Interface;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Serializer_Interface;
class Config_Loader implements Config_Loader_Interface
{
    /**
     * Config reader
     *
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected $_reader;
    /**
     * Config reader factory
     *
     * @var \Magento\Framework\ObjectManager\Config\Reader\DomFactory
     */
    protected $_reader_factory;
    /**
     * @var \Magento\Framework\Config\CacheInterface
     */
    protected $_cache;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\ObjectManager\Config\Reader\DomFactory $readerFactory
     * @param Serialize|null $serializer
     */
    public function __construct(\Magento\Framework\Config\Cache_Interface $cache, \Magento\Framework\Object_Manager\Config\Reader\Dom_Factory $reader_factory, ?Serialize $serializer = null)
    {
        $this->_cache = $cache;
        $this->_reader_factory = $reader_factory;
        $this->serializer = $serializer ?? \Magento\Framework\App\Object_Manager::get_instance()->get(Serialize::class);
    }
    /**
     * Get reader instance
     *
     * @return \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected function _get_reader()
    {
        if (empty($this->_reader)) {
            $this->_reader = $this->_reader_factory->create();
        }
        return $this->_reader;
    }
    /**
     * @inheritdoc
     */
    public function load($area)
    {
        $cache_id = $area . '::DiConfig';
        $data = $this->_cache->load($cache_id);
        if (!$data) {
            $data = $this->_get_reader()->read($area);
            $this->_cache->save($this->serializer->serialize($data), $cache_id);
        } else {
            $data = $this->serializer->unserialize($data);
        }
        return $data;
    }
}