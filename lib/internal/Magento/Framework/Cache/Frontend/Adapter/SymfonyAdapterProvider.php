<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\Magento_Database_Adapter;
use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters\Filesystem_Tag_Adapter;
use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters\Generic_Tag_Adapter;
use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters\Redis_Tag_Adapter;
use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapters\Tag_Adapter_Interface;
use Magento\Framework\Filesystem;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Serialize\Serializer\Serialize;
use Predis\Client as PredisClient;
use Psr\Cache\Cache_Item_Pool_Interface;
use Symfony\Component\Cache\Adapter\Adapter_Interface;
use Symfony\Component\Cache\Adapter\Apcu_Adapter;
use Symfony\Component\Cache\Adapter\Chain_Adapter;
use Symfony\Component\Cache\Adapter\Filesystem_Adapter;
use Symfony\Component\Cache\Adapter\Memcached_Adapter;
use Symfony\Component\Cache\Adapter\Redis_Adapter;
use Symfony\Component\Cache\Adapter\Tag_Aware_Adapter;
use Symfony\Component\Cache\Marshaller\Default_Marshaller;
/**
 * Symfony cache adapter factory
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Symfony_Adapter_Provider implements Reset_After_Request_Interface
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;
    /**
     * @var ResourceConnection
     */
    private Resource_Connection $resource;
    public const REDIS_MAX_LIFETIME = 2592000;
    public const REDIS_DEFAULT_CONNECT_TIMEOUT = 2.5;
    public const REDIS_DEFAULT_CONNECT_RETRIES = 1;
    /**
     * @var Serialize
     */
    private Serialize $serializer;
    /**
     * @var array<string, mixed>
     */
    private array $connection_pool = [];
    /**
     * Cached adapter type mappings (lowercase => canonical)
     *
     * @var array<string, string>
     */
    private array $adapter_type_map = [
        // Redis backends
        'redis' => 'redis',
        // Valkey backends
        'valkey' => 'redis',
        // Memcached backends
        'memcached' => 'memcached',
        'libmemcached' => 'memcached',
        // File backends
        'file' => 'filesystem',
        // Database backend
        'database' => 'database',
        // APCu backends
        'apc' => 'apcu',
        'apcu' => 'apcu',
        // Two-level cache
        'two_levels' => 'twolevel',
        'twolevel' => 'twolevel',
    ];
    /**
     * @param Filesystem $filesystem
     * @param ResourceConnection $resource
     * @param Serialize $serializer PHP native serializer
     */
    public function __construct(Filesystem $filesystem, Resource_Connection $resource, Serialize $serializer)
    {
        $this->filesystem = $filesystem;
        $this->resource = $resource;
        $this->serializer = $serializer;
    }
    /**
     * Reset state for Application Server (Swoole) request handling
     *
     * Called by ObjectManager::_resetState() between HTTP requests in Application Server mode.
     * Clears connection pool to prevent stale connections and state pollution across requests.
     *
     * @return void
     */
    public function _reset_state(): void
    {
        $this->connection_pool = [];
    }
    /**
     * Create Symfony cache adapter based on backend type and options
     *
     * @param string $backendType
     * @param array $backendOptions
     * @param string $namespace Cache namespace/prefix
     * @param int|null $defaultLifetime
     * @return CacheItemPoolInterface
     * @throws \Exception
     */
    public function create_adapter(string $backend_type, array $backend_options, string $namespace = '', ?int $default_lifetime = null): Cache_Item_Pool_Interface
    {
        // Optimize: Use pre-built map instead of switch
        $backend_type_lower = strtolower($backend_type);
        $resolved_type = $this->adapter_type_map[$backend_type_lower] ?? 'filesystem';
        // Create adapter based on resolved type with fallback to filesystem
        try {
            $adapter = match ($resolved_type) {
                'redis' => $this->create_redis_adapter($backend_options, $namespace, $default_lifetime),
                'memcached' => $this->create_memcached_adapter($backend_options, $namespace, $default_lifetime),
                'filesystem' => $this->create_filesystem_adapter($backend_options, $namespace, $default_lifetime),
                'database' => $this->create_database_adapter($backend_options, $namespace, $default_lifetime),
                'apcu' => $this->create_apcu_adapter($namespace, $default_lifetime),
                'twolevel' => $this->create_two_level_adapter($backend_options, $namespace, $default_lifetime),
                default => $this->create_filesystem_adapter($backend_options, $namespace, $default_lifetime),
            };
        } catch (\Exception $e) {
            // Fallback to filesystem adapter if the requested adapter fails
            $adapter = $this->create_filesystem_adapter($backend_options, $namespace, $default_lifetime);
        }
        // Skip TagAwareAdapter for Redis/Filesystem (native tag support)
        if (in_array($resolved_type, ['redis', 'filesystem'], true)) {
            return $adapter;
        }
        return new Tag_Aware_Adapter($adapter);
    }
    /**
     * Create appropriate tag adapter based on backend type
     *
     * @param string $backendType
     * @param CacheItemPoolInterface $cachePool
     * @param string $namespace
     * @param bool $isPageCache
     * @param array $backendOptions
     * @return TagAdapterInterface
     */
    public function create_tag_adapter(string $backend_type, Cache_Item_Pool_Interface $cache_pool, string $namespace = '', bool $is_page_cache = false, array $backend_options = []): Tag_Adapter_Interface
    {
        // Resolve backend type
        $backend_type_lower = strtolower($backend_type);
        $resolved_type = $this->adapter_type_map[$backend_type_lower] ?? 'filesystem';
        // Check if Lua scripts are enabled (separate flags for different operations)
        $use_lua = !empty($backend_options['use_lua']) && $backend_options['use_lua'] === '1';
        $use_lua_on_gc = !empty($backend_options['use_lua_on_gc']) && $backend_options['use_lua_on_gc'] === '1';
        // Create appropriate tag adapter with fallback to GenericTagAdapter
        try {
            return match ($resolved_type) {
                'redis' => new Redis_Tag_Adapter($cache_pool, $namespace, $use_lua, $use_lua_on_gc),
                'filesystem' => new Filesystem_Tag_Adapter($cache_pool, $this->get_cache_directory()),
                default => new Generic_Tag_Adapter($cache_pool, $is_page_cache),
            };
        } catch (\Exception $e) {
            // Fallback to GenericTagAdapter if specialized adapter creation fails
            return new Generic_Tag_Adapter($cache_pool, $is_page_cache);
        }
    }
    /**
     * Get cache directory for filesystem operations
     *
     * @return string
     */
    private function get_cache_directory(): string
    {
        // Use Magento's var/cache directory via Filesystem
        $cache_dir = $this->filesystem->get_directory_read(Directory_List::CACHE);
        return $cache_dir->get_absolute_path() . 'symfony';
    }
    /**
     * Create Redis cache adapter with automatic fallback support
     *
     * @param array $options Connection and configuration options
     * @param string $namespace Cache key namespace/prefix
     * @param int|null $defaultLifetime Default cache lifetime in seconds
     * @return AdapterInterface
     * @throws \RuntimeException If neither phpredis nor Predis is available
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function create_redis_adapter(array $options, string $namespace, ?int $default_lifetime): Adapter_Interface
    {
        // Extract connection parameters (optimized with null coalescing)
        $host = $options['server'] ?? $options['host'] ?? '127.0.0.1';
        $port = (int) ($options['port'] ?? 6379);
        $password = $options['password'] ?? null;
        $database = (int) ($options['database'] ?? 0);
        // OPTIMIZATION: Auto-enable igbinary if available (2-3x faster serialization)
        $serializer = $options['serializer'] ?? null;
        if ($serializer === null && extension_loaded('igbinary')) {
            $serializer = 'igbinary';
        }
        // Persistent connection support (15-30% performance gain)
        $persistent = isset($options['persistent']) ? (bool) $options['persistent'] : true;
        // Enable by default
        $persistent_id = $options['persistent_id'] ?? null;
        // Connection tuning parameters with Zend-compatible defaults
        $timeout = isset($options['timeout']) ? (float) $options['timeout'] : self::REDIS_DEFAULT_CONNECT_TIMEOUT;
        $read_timeout = isset($options['read_timeout']) ? (float) $options['read_timeout'] : null;
        $retry_interval = isset($options['retry_interval']) ? (int) $options['retry_interval'] : null;
        $connect_retries = isset($options['connect_retries']) ? (int) $options['connect_retries'] : self::REDIS_DEFAULT_CONNECT_RETRIES;
        // For Predis with Ultra-Optimized client: use connection pooling like phpredis
        // The client's internal cache is cleared on writes and database switches,
        // so connection pooling is safe and provides better performance
        $use_php_redis = extension_loaded('redis');
        $connection_key = sprintf('redis:%s:%d:%d', $host, $port, $database);
        if (!isset($this->connection_pool[$connection_key])) {
            if ($use_php_redis) {
                $this->connection_pool[$connection_key] = $this->create_php_redis_connection($host, $port, $password, $database, $persistent, $persistent_id, $timeout, $read_timeout, $retry_interval, $connect_retries);
            } elseif (class_exists(Predis_Client::class)) {
                $this->connection_pool[$connection_key] = $this->create_optimized_predis_connection($host, $port, $password, $database, $persistent, $timeout, $read_timeout);
            } else {
                throw new \RuntimeException('Redis cache requires either phpredis extension or predis/predis library. ' . 'Install phpredis extension (recommended) or run: composer require predis/predis');
            }
        }
        // Set client name every time (even for pooled connections)
        if ($persistent_id) {
            $this->set_redis_client_name($this->connection_pool[$connection_key], $persistent_id);
        }
        // Create marshaller with igbinary support if configured
        $marshaller = $this->create_marshaller($serializer);
        return new Redis_Adapter($this->connection_pool[$connection_key], $namespace, $default_lifetime ?? 0, $marshaller);
    }
    /**
     * Create phpredis connection (native C extension)
     *
     * This is the recommended and fastest option for Redis connectivity.
     *
     * @param string $host
     * @param int $port
     * @param string|null $password
     * @param int $database
     * @param bool $persistent
     * @param string|null $persistentId
     * @param float|null $timeout
     * @param float|null $readTimeout
     * @param int|null $retryInterval
     * @param int|null $connectRetries
     * @return \Redis|\RedisCluster|\Relay\Relay
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function create_php_redis_connection(string $host, int $port, ?string $password, int $database, bool $persistent, ?string $persistent_id, ?float $timeout, ?float $read_timeout, ?int $retry_interval, ?int $connect_retries)
    {
        // Build optimized DSN with all connection parameters
        $dsn_params = [];
        // Add persistent connection parameters
        if ($persistent) {
            $dsn_params[] = 'persistent=1';
            if ($persistent_id) {
                $dsn_params[] = 'persistent_id=' . urlencode($persistent_id);
            }
        }
        // Add connection timeout parameters
        if ($timeout !== null) {
            $dsn_params[] = 'timeout=' . $timeout;
        }
        if ($read_timeout !== null) {
            $dsn_params[] = 'read_timeout=' . $read_timeout;
        }
        // Add retry parameters
        if ($retry_interval !== null) {
            $dsn_params[] = 'retry_interval=' . $retry_interval;
        }
        if ($connect_retries !== null) {
            $dsn_params[] = 'connect_retries=' . $connect_retries;
        }
        // Build base DSN
        $base_dsn = $password ? sprintf('redis://%s@%s:%d/%d', urlencode($password), $host, $port, $database) : sprintf('redis://%s:%d/%d', $host, $port, $database);
        // Append DSN parameters
        $dsn = $dsn_params ? $base_dsn . '?' . implode('&', $dsn_params) : $base_dsn;
        // Create and return the connection using Symfony's factory
        return Redis_Adapter::create_connection($dsn);
    }
    /**
     * Create ultra-optimized Predis connection (Symfony-compatible, maximum performance)
     *
     * @param string $host
     * @param int $port
     * @param string|null $password
     * @param int $database
     * @param bool $persistent
     * @param float|null $timeout
     * @param float|null $readTimeout
     * @return OptimizedPredisClient
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function create_optimized_predis_connection(string $host, int $port, ?string $password, int $database, bool $persistent, ?float $timeout, ?float $read_timeout)
    {
        $params = ['scheme' => 'tcp', 'host' => $host, 'port' => $port, 'database' => $database];
        if ($password) {
            $params['password'] = $password;
        }
        $options = ['exceptions' => false];
        return new Optimized_Predis_Client($params, $options);
    }
    /**
     * Set Redis client name for better monitoring and debugging
     *
     * @param mixed $connection Redis connection (phpredis, RedisCluster, Relay, or Predis)
     * @param string $clientName Name to set for the client
     * @return void
     */
    private function set_redis_client_name($connection, string $client_name): void
    {
        try {
            // Set Redis client name for better monitoring
            if ($connection instanceof \Redis) {
                // phpredis
                $connection->client('SETNAME', $client_name);
                // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
            } elseif ($connection instanceof \Redis_Cluster) {
                // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
            } elseif ($connection instanceof Predis_Client) {
                // Predis
                $connection->client('SETNAME', $client_name);
            } elseif (method_exists($connection, 'client')) {
                // Relay or other compatible implementations
                $connection->client('SETNAME', $client_name);
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        } catch (\Exception $e) {
            // Intentional no-op: Client name is for monitoring only, failures are non-critical
        }
    }
    /**
     * Create marshaller for serialization
     *
     * Supports igbinary for 70% faster serialization and 58% smaller data size
     *
     * @param string|null $serializer Serializer name ('igbinary' or null for default)
     * @return DefaultMarshaller|null
     */
    private function create_marshaller(?string $serializer): ?Default_Marshaller
    {
        // If no serializer specified or not 'igbinary', return null (uses default PHP serializer)
        if ($serializer !== 'igbinary') {
            return null;
        }
        // Check if igbinary extension is loaded
        if (!extension_loaded('igbinary')) {
            // Fallback to default PHP serializer if igbinary not available
            return null;
        }
        // Create marshaller with igbinary enabled, true = use igbinary_serialize/igbinary_unserialize
        // false = don't throw on serialization failure (graceful degradation)
        return new Default_Marshaller(true, false);
    }
    /**
     * Create Memcached cache adapter
     *
     * @param array $options
     * @param string $namespace
     * @param int|null $defaultLifetime
     * @return AdapterInterface
     */
    private function create_memcached_adapter(array $options, string $namespace, ?int $default_lifetime): Adapter_Interface
    {
        // Build server list (optimized)
        if (isset($options['servers'])) {
            // Multiple servers - optimize with direct assignment
            $servers = [];
            foreach ($options['servers'] as $server) {
                $servers[] = [$server[0] ?? '127.0.0.1', $server[1] ?? 11211];
            }
            // phpcs:ignore Magento2.Security.InsecureFunction,Magento2.Functions.DiscouragedFunction
            $connection_key = 'memcached:' . md5(serialize($servers));
        } else {
            // Single server - fast path
            $host = $options['server'] ?? $options['host'] ?? '127.0.0.1';
            $port = $options['port'] ?? 11211;
            $servers = [[$host, $port]];
            $connection_key = sprintf('memcached:%s:%d', $host, $port);
        }
        // Check connection pool
        if (!isset($this->connection_pool[$connection_key])) {
            $this->connection_pool[$connection_key] = Memcached_Adapter::create_connection($servers);
        }
        return new Memcached_Adapter($this->connection_pool[$connection_key], $namespace, $default_lifetime ?? 0);
    }
    /**
     * Create Filesystem cache adapter
     *
     * @param array $options
     * @param string $namespace
     * @param int|null $defaultLifetime
     * @return AdapterInterface
     */
    private function create_filesystem_adapter(array $options, string $namespace, ?int $default_lifetime): Adapter_Interface
    {
        // Get cache directory (optimized path)
        if (isset($options['cache_dir'])) {
            $cache_dir = $options['cache_dir'];
        } else {
            // Cache the directory path for reuse
            static $default_cache_dir = null;
            if ($default_cache_dir === null) {
                $directory = $this->filesystem->get_directory_write(Directory_List::CACHE);
                $default_cache_dir = $directory->get_absolute_path();
                $directory->create();
            }
            $cache_dir = $default_cache_dir;
        }
        // Add igbinary marshaller support for file cache (70% faster, 58% smaller)
        $serializer = $options['serializer'] ?? null;
        $marshaller = $this->create_marshaller($serializer);
        return new Filesystem_Adapter($namespace, $default_lifetime ?? 0, $cache_dir, $marshaller);
    }
    /**
     * Create Magento Database cache adapter
     *
     * @param array $options Backend options (unused - database config is in ResourceConnection)
     * @param string $namespace
     * @param int|null $defaultLifetime
     * @return CacheItemPoolInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function create_database_adapter(array $options, string $namespace, ?int $default_lifetime): Cache_Item_Pool_Interface
    {
        // Use Magento's existing Database backend (reuses cache/cache_tag tables)
        return new Magento_Database_Adapter($this->resource, $this->serializer, $namespace, $default_lifetime ?? 0);
    }
    /**
     * Create APCu cache adapter
     *
     * @param string $namespace
     * @param int|null $defaultLifetime
     * @return AdapterInterface
     */
    private function create_apcu_adapter(string $namespace, ?int $default_lifetime): Adapter_Interface
    {
        return new Apcu_Adapter($namespace, $default_lifetime ?? 0);
    }
    /**
     * Create two-level cache adapter (fast + persistent)
     *
     * Performance optimizations:
     * - Cached extension checks
     * - Optimized adapter selection
     * - String operation optimization
     *
     * @param array $options
     * @param string $namespace
     * @param int|null $defaultLifetime
     * @return AdapterInterface
     */
    private function create_two_level_adapter(array $options, string $namespace, ?int $default_lifetime): Adapter_Interface
    {
        $adapters = [];
        // Fast cache (APCu or Filesystem) - cached extension check
        static $apcu_available = null;
        if ($apcu_available === null) {
            $apcu_available = extension_loaded('apcu') && ini_get('apc.enabled');
        }
        if ($apcu_available) {
            $adapters[] = $this->create_apcu_adapter($namespace . '_fast', $default_lifetime);
        } else {
            $fast_options = $options['fast_backend_options'] ?? [];
            $adapters[] = $this->create_filesystem_adapter($fast_options, $namespace . '_fast', $default_lifetime);
        }
        // Persistent cache (Redis or Filesystem) - optimized type check
        $slow_options = $options['slow_backend_options'] ?? [];
        $slow_type = strtolower($options['slow_backend'] ?? 'file');
        if ($slow_type === 'redis') {
            $adapters[] = $this->create_redis_adapter($slow_options, $namespace . '_slow', $default_lifetime);
        } else {
            $adapters[] = $this->create_filesystem_adapter($slow_options, $namespace . '_slow', $default_lifetime);
        }
        return new Chain_Adapter($adapters, $default_lifetime ?? 0);
    }
}