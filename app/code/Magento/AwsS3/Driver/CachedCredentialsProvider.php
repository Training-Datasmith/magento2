<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Aws\Credentials\CredentialProvider;

class CachedCredentialsProvider
{
    public function __construct(private readonly CredentialsCache $magentoCacheAdapter)
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
            [CredentialProvider::class, 'cache'],
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            call_user_func([CredentialProvider::class, 'defaultProvider']),
            $this->magentoCacheAdapter
        );
    }
}
