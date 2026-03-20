<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Aws_S3\Model;

use Magento\Framework\App\Deployment_Config;
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
    public function __construct(private readonly Deployment_Config $config)
    {
    }
    /**
     * Retrieves endpoint.
     */
    public function get_endpoint(): string
    {
        return (string) $this->config->get(self::PATH_ENDPOINT);
    }
    /**
     * Retrieves region.
     */
    public function get_region(): string
    {
        return (string) $this->config->get(self::PATH_REGION);
    }
    /**
     * Retrieves bucket.
     */
    public function get_bucket(): string
    {
        return (string) $this->config->get(self::PATH_BUCKET);
    }
    /**
     * Retrieves access key.
     */
    public function get_access_key(): string
    {
        return (string) $this->config->get(self::PATH_ACCESS_KEY);
    }
    /**
     * Retrieves secret key.
     */
    public function get_secret_key(): string
    {
        return (string) $this->config->get(self::PATH_SECRET_KEY);
    }
    /**
     * Retrieves prefix.
     */
    public function get_prefix(): string
    {
        return (string) $this->config->get(self::PATH_PREFIX, '');
    }
    /**
     * Retrieves endpoint.
     */
    public function get_path_style(): string
    {
        return (string) $this->config->get(self::PATH_PATH_STYLE, '0');
    }
}