<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Aws_S3\Driver;

use Aws\Credentials\Credential_Provider;
class Cached_Credentials_Provider
{
    public function __construct(private readonly Credentials_Cache $magento_cache_adapter)
    {
    }
    /**
     * Provides cache mechanism to retrieve and store AWS credentials
     *
     * @return callable
     */
    public function get(): mixed
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        return call_user_func(
            [Credential_Provider::class, 'cache'],
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            call_user_func([Credential_Provider::class, 'defaultProvider']),
            $this->magento_cache_adapter
        );
    }
}