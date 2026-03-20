<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Provides communication configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Communication\Config\CompositeReader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(\Magento\Framework\Communication\Config\Composite_Reader $reader, \Magento\Framework\Config\Cache_Interface $cache, $cache_id = 'communication_config_cache', ?Serializer_Interface $serializer = null)
    {
        parent::__construct($reader, $cache, $cache_id, $serializer);
    }
}