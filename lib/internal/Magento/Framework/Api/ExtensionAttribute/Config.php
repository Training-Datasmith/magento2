<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Extension_Attribute;

use Magento\Framework\Api\Extension_Attribute\Config\Reader;
use Magento\Framework\Config\Cache_Interface;
use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Extension attributes config
 */
class Config extends \Magento\Framework\Config\Data
{
    /**
     * Cache identifier
     */
    public const CACHE_ID = 'extension_attributes_config';
    /**
     * Constructor
     *
     * @param Reader $reader
     * @param CacheInterface $cache
     * @param string $cacheId|null
     * @param SerializerInterface|null $serializer
     */
    public function __construct(Reader $reader, Cache_Interface $cache, $cache_id = self::CACHE_ID, ?Serializer_Interface $serializer = null)
    {
        parent::__construct($reader, $cache, $cache_id, $serializer);
    }
}