<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Aws_S3\Driver;

use Aws\S3\S3Client;
use League\Flysystem\Aws_S3v3\Aws_S3v3adapter;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Object_Manager_Interface;
use Magento\Remote_Storage\Driver\Adapter\Cache\Cache_Interface_Factory;
use Magento\Remote_Storage\Driver\Adapter\Cached_Adapter_Interface_Factory;
use Magento\Remote_Storage\Driver\Adapter\Metadata_Provider_Interface_Factory;
use Magento\Remote_Storage\Driver\Driver_Exception;
use Magento\Remote_Storage\Driver\Driver_Factory_Interface;
use Magento\Remote_Storage\Driver\Remote_Driver_Interface;
use Magento\Remote_Storage\Model\Config;
/**
 * Creates a pre-configured instance of AWS S3 driver.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Aws_S3factory implements Driver_Factory_Interface
{
    /**
     * @var MetadataProviderInterfaceFactory
     */
    private $metadata_provider_factory;
    /**
     * @var CacheInterfaceFactory
     */
    private $cache_interface_factory;
    /**
     * @var CachedAdapterInterfaceFactory
     */
    private $cached_adapter_interface_factory;
    /**
     * @var CachedCredentialsProvider
     */
    private $cached_credentials_provider;
    public function __construct(private readonly Object_Manager_Interface $object_manager, private readonly Config $config, Metadata_Provider_Interface_Factory $metadata_provider_factory, Cache_Interface_Factory $cache_interface_factory, Cached_Adapter_Interface_Factory $cached_adapter_interface_factory, private readonly ?string $cache_prefix = null, ?Cached_Credentials_Provider $cached_credentials_provider = null)
    {
        $this->metadata_provider_factory = $metadata_provider_factory;
        $this->cache_interface_factory = $cache_interface_factory;
        $this->cached_adapter_interface_factory = $cached_adapter_interface_factory;
        $this->cached_credentials_provider = $cached_credentials_provider ?? $this->object_manager->get(Cached_Credentials_Provider::class);
    }
    /**
     * @inheritDoc
     */
    public function create(): Remote_Driver_Interface
    {
        try {
            return $this->create_configured($this->config->get_config(), $this->config->get_prefix());
        } catch (Localized_Exception $exception) {
            throw new Driver_Exception(__($exception->get_message()), $exception);
        }
    }
    /**
     * Prepare config for S3Client
     *
     * @throws DriverException
     */
    private function prepare_config(array $config): array
    {
        $config['version'] = 'latest';
        if (empty($config['credentials']['key']) || empty($config['credentials']['secret'])) {
            //Access keys were not provided; request token from AWS config (local or EC2) and cache result
            $config['credentials'] = $this->cached_credentials_provider->get();
        }
        if (empty($config['bucket']) || empty($config['region'])) {
            throw new Driver_Exception(__('Bucket and region are required values'));
        }
        if (!empty($config['http_handler'])) {
            $config['http_handler'] = $this->object_manager->create($config['http_handler'])($config);
        }
        if (!empty($config['path_style'])) {
            $config['use_path_style_endpoint'] = boolval($config['path_style']);
        }
        return $config;
    }
    /**
     * @inheritDoc
     */
    public function create_configured(array $config, string $prefix, string $cache_adapter = '', array $cache_config = []): Remote_Driver_Interface
    {
        $config = $this->prepare_config($config);
        $client = new S3Client($config);
        $adapter = new Aws_S3v3adapter($client, $config['bucket'], $prefix);
        $cache = $this->cache_interface_factory->create(
            // Custom cache prefix required to distinguish cache records for different sources.
            // phpcs:ignore Magento2.Security.InsecureFunction
            $this->cache_prefix ? ['prefix' => $this->cache_prefix] : ['prefix' => md5($config['bucket'] . $prefix)]
        );
        $metadata_provider = $this->metadata_provider_factory->create(['adapter' => $adapter, 'cache' => $cache]);
        $object_url = rtrim($client->get_object_url($config['bucket'], './'), '/') . trim($prefix, '\/') . '/';
        return $this->object_manager->create(Aws_S3::class, ['adapter' => $this->cached_adapter_interface_factory->create(['adapter' => $adapter, 'cache' => $cache, 'metadataProvider' => $metadata_provider]), 'objectUrl' => $object_url, 'metadataProvider' => $metadata_provider]);
    }
}