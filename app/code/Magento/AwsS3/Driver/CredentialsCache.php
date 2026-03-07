<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Aws\CacheInterface;
use Aws\Credentials\CredentialsFactory;
use Magento\Framework\App\CacheInterface as MagentoCacheInterface;
use Magento\Framework\Serialize\Serializer\Json;

/** Cache Adapter for AWS credentials */
class CredentialsCache implements CacheInterface
{
    /**
     * @var CredentialsFactory
     */
    private $credentialsFactory;

    public function __construct(private readonly MagentoCacheInterface $magentoCache, CredentialsFactory $credentialsFactory, private readonly Json $json)
    {
        $this->credentialsFactory = $credentialsFactory;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $value = $this->magentoCache->load($key);

        if (!is_string($value)) {
            return null;
        }

        $result = $this->json->unserialize($value);
        try {
            return $this->credentialsFactory->create($result);
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
            $value = $value->toArray();
        }
        $this->magentoCache->save($this->json->serialize($value), $key, [], $ttl);
    }

    /**
     * @inheritdoc
     */
    public function remove($key): void
    {
        $this->magentoCache->remove($key);
    }
}
