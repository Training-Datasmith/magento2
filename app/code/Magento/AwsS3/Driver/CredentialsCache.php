<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Aws_S3\Driver;

use Aws\Cache_Interface;
use Aws\Credentials\Credentials_Factory;
use Magento\Framework\App\Cache_Interface as MagentoCacheInterface;
use Magento\Framework\Serialize\Serializer\Json;
/** Cache Adapter for AWS credentials */
class Credentials_Cache implements Cache_Interface
{
    /**
     * @var CredentialsFactory
     */
    private $credentials_factory;
    public function __construct(private readonly Magento_Cache_Interface $magento_cache, Credentials_Factory $credentials_factory, private readonly Json $json)
    {
        $this->credentials_factory = $credentials_factory;
    }
    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $value = $this->magento_cache->load($key);
        if (!is_string($value)) {
            return null;
        }
        $result = $this->json->unserialize($value);
        try {
            return $this->credentials_factory->create($result);
        } catch (\Exception) {
            return $result;
        }
    }
    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = 0): void
    {
        if (method_exists($value, 'toArray')) {
            $value = $value->to_array();
        }
        $this->magento_cache->save($this->json->serialize($value), $key, [], $ttl);
    }
    /**
     * @inheritdoc
     */
    public function remove($key): void
    {
        $this->magento_cache->remove($key);
    }
}