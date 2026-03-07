<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Model;

use Magento\Framework\App\DeploymentConfig;

/**
 * Configuration for AWS S3.
 */
class Config
{
    public const PATH_ENDPOINT = 'remote_storage/endpoint';
    public const PATH_REGION = 'remote_storage/region';
    public const PATH_BUCKET = 'remote_storage/bucket';
    public const PATH_ACCESS_KEY = 'remote_storage/access_key';
    public const PATH_SECRET_KEY = 'remote_storage/secret_key';
    public const PATH_PREFIX = 'remote_storage/prefix';
    public const PATH_PATH_STYLE = 'remote_storage/path_style';

    public function __construct(private readonly DeploymentConfig $config)
    {
    }

    /**
     * Retrieves endpoint.
     */
    public function getEndpoint(): string
    {
        return (string)$this->config->get(self::PATH_ENDPOINT);
    }

    /**
     * Retrieves region.
     */
    public function getRegion(): string
    {
        return (string)$this->config->get(self::PATH_REGION);
    }

    /**
     * Retrieves bucket.
     */
    public function getBucket(): string
    {
        return (string)$this->config->get(self::PATH_BUCKET);
    }

    /**
     * Retrieves access key.
     */
    public function getAccessKey(): string
    {
        return (string)$this->config->get(self::PATH_ACCESS_KEY);
    }

    /**
     * Retrieves secret key.
     */
    public function getSecretKey(): string
    {
        return (string)$this->config->get(self::PATH_SECRET_KEY);
    }

    /**
     * Retrieves prefix.
     */
    public function getPrefix(): string
    {
        return (string)$this->config->get(self::PATH_PREFIX, '');
    }

    /**
     * Retrieves endpoint.
     */
    public function getPathStyle(): string
    {
        return (string)$this->config->get(self::PATH_PATH_STYLE, '0');
    }
}
