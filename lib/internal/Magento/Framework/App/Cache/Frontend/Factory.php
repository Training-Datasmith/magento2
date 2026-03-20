<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Factory that creates cache frontend instances based on options
 */
namespace Magento\Framework\App\Cache\Frontend;

use Exception;
use LogicException;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Cache\Backend\Database;
use Magento\Framework\Cache\Backend\Eaccelerator;
use Magento\Framework\Cache\Backend\Remote_Synchronized_Cache;
use Magento\Framework\Cache\Frontend\Adapter\Preloading_Symfony_Adapter;
use Magento\Framework\Cache\Frontend\Adapter\Symfony;
use Magento\Framework\Cache\Frontend\Adapter\Symfony_Adapter_Provider;
use Magento\Framework\Cache\Frontend\Decorator\Compression as CompressionDecorator;
use Magento\Framework\Cache\Frontend_Interface;
use Magento\Framework\Filesystem;
use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Profiler;
use UnexpectedValueException;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Factory
{
    /**
     * Default cache entry lifetime
     */
    public const DEFAULT_LIFETIME = 7200;
    /**
     * Caching params, that applied for all cache frontends regardless of type
     */
    public const PARAM_CACHE_FORCED_OPTIONS = 'cache_options';
    /**
     * @var ObjectManagerInterface
     */
    private $_object_manager;
    /**
     * @var Filesystem
     */
    private $_filesystem;
    /**
     * Cache options to be enforced for all instances being created
     *
     * @var array
     */
    private $_enforced_options = [];
    /**
     * Configuration of decorators that are to be applied to every cache frontend being instantiated, format:
     * array(
     *  array('class' => '<decorator_class>', 'arguments' => array()),
     *  ...
     * )
     *
     * @var array
     */
    private $_decorators = [];
    /**
     * Default cache backend type
     *
     * @var string
     */
    protected $_default_backend = 'file';
    /**
     * Options for default backend
     *
     * @var array
     */
    protected $_backend_options = ['hashed_directory_level' => 1, 'file_name_prefix' => 'mage'];
    /**
     * @var ResourceConnection
     */
    protected $_resource;
    /**
     * SymfonyAdapterProvider instance for creating Symfony cache adapters
     *
     * @var SymfonyAdapterProvider
     */
    private Symfony_Adapter_Provider $adapter_provider;
    /**
     * Cached directory paths (performance optimization)
     *
     * @var array
     */
    private array $cached_directories = [];
    /**
     * Cached extension availability checks (performance optimization)
     *
     * @var array
     */
    private array $extension_cache = [];
    /**
     * Cached ID prefix (performance optimization)
     *
     * @var string|null
     */
    private ?string $cached_id_prefix = null;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param Filesystem $filesystem
     * @param ResourceConnection $resource
     * @param SymfonyAdapterProvider $adapterProvider
     * @param array $enforcedOptions
     * @param array $decorators
     */
    public function __construct(Object_Manager_Interface $object_manager, Filesystem $filesystem, Resource_Connection $resource, Symfony_Adapter_Provider $adapter_provider, array $enforced_options = [], array $decorators = [])
    {
        $this->_object_manager = $object_manager;
        $this->_filesystem = $filesystem;
        $this->_resource = $resource;
        $this->adapter_provider = $adapter_provider;
        $this->_enforced_options = $enforced_options;
        $this->_decorators = $decorators;
    }
    /**
     * Return newly created cache frontend instance
     *
     * @param array $options
     * @return FrontendInterface
     */
    public function create(array $options)
    {
        $options = $this->_get_expanded_options($options);
        // Optimize: Cache directory operations
        foreach (['backend_options', 'slow_backend_options'] as $section) {
            if (!empty($options[$section]['cache_dir'])) {
                $cache_dir = $options[$section]['cache_dir'];
                if (!isset($this->cached_directories[$cache_dir])) {
                    $directory = $this->_filesystem->get_directory_write(Directory_List::VAR_DIR);
                    $directory->create($cache_dir);
                    $this->cached_directories[$cache_dir] = $directory->get_absolute_path($cache_dir);
                }
                $options[$section]['cache_dir'] = $this->cached_directories[$cache_dir];
            }
        }
        // Optimize: Use cached ID prefix or generate once
        $id_prefix = $this->get_id_prefix($options);
        $options['frontend_options']['cache_id_prefix'] = $id_prefix;
        $backend = $this->_get_backend_options($options);
        $frontend = $this->_get_frontend_options($options);
        // Start profiling
        $profiler_tags = ['group' => 'cache', 'operation' => 'cache:create', 'frontend_type' => $frontend['type'], 'backend_type' => $backend['type']];
        Profiler::start('cache_frontend_create', $profiler_tags);
        // Check for special backend types
        $backend_type = $options['backend'] ?? $this->_default_backend;
        if ($this->is_symfony_l2cache($backend_type)) {
            // SymfonyL2Cache backend for L2 cache with Symfony
            $result = $this->create_symfony_l2cache($options);
        } else {
            // Use Symfony cache - fully backward compatible, no Zend cache needed
            $result = $this->create_symfony_cache($options);
        }
        $result = $this->_apply_decorators($result);
        // stop profiling
        Profiler::stop('cache_frontend_create');
        return $result;
    }
    /**
     * Get or generate cache ID prefix (optimized with caching)
     *
     * @param array $options
     * @return string
     */
    private function get_id_prefix(array $options): string
    {
        // Check explicit prefix in options
        $id_prefix = $options['id_prefix'] ?? $options['prefix'] ?? '';
        if (!empty($id_prefix)) {
            return $id_prefix;
        }
        // Use cached prefix if available
        if ($this->cached_id_prefix !== null) {
            return $this->cached_id_prefix;
        }
        // Generate and cache prefix
        $config_dir_path = $this->_filesystem->get_directory_read(Directory_List::CONFIG)->get_absolute_path();
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $this->cached_id_prefix = substr(md5($config_dir_path), 0, 3) . '_';
        return $this->cached_id_prefix;
    }
    /**
     * Return options expanded with enforced values
     *
     * @param array $options
     * @return array
     */
    private function _get_expanded_options(array $options)
    {
        return array_replace_recursive($options, $this->_enforced_options);
    }
    /**
     * Apply decorators to a cache frontend instance and return the topmost one
     *
     * @param FrontendInterface $frontend
     * @return FrontendInterface
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    private function _apply_decorators(Frontend_Interface $frontend)
    {
        foreach ($this->_decorators as $decorator_config) {
            if (!isset($decorator_config['class'])) {
                throw new LogicException('Class has to be specified for a cache frontend decorator.');
            }
            $decorator_class = $decorator_config['class'];
            $decorator_params = isset($decorator_config['parameters']) ? $decorator_config['parameters'] : [];
            $decorator_params['frontend'] = $frontend;
            // conventionally, 'frontend' argument is a decoration subject
            $frontend = $this->_object_manager->create($decorator_class, $decorator_params);
            if (!$frontend instanceof Frontend_Interface) {
                throw new UnexpectedValueException('Decorator has to implement the cache frontend interface.');
            }
        }
        return $frontend;
    }
    /**
     * Check if extension is loaded (cached for performance)
     *
     * @param string $extension
     * @return bool
     */
    private function is_extension_loaded(string $extension): bool
    {
        if (!isset($this->extension_cache[$extension])) {
            $this->extension_cache[$extension] = extension_loaded($extension);
        }
        return $this->extension_cache[$extension];
    }
    /**
     * Get cache backend options. Result array contain backend type ('type' key) and backend options ('options')
     *
     * @param  array $cacheOptions
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _get_backend_options(array $cache_options)
    {
        $enable_two_levels = false;
        $type = $cache_options['backend'] ?? $this->_default_backend;
        $options = isset($cache_options['backend_options']) && is_array($cache_options['backend_options']) ? $cache_options['backend_options'] : [];
        $backend_type = false;
        $type_lower = strtolower($type);
        switch ($type_lower) {
            case 'sqlite':
                if ($this->is_extension_loaded('sqlite') && isset($options['cache_db_complete_path'])) {
                    $backend_type = 'Sqlite';
                }
                break;
            case 'memcached':
                if ($this->is_extension_loaded('memcached')) {
                    if (isset($cache_options['memcached'])) {
                        $options = $cache_options['memcached'];
                    }
                    $enable_two_levels = true;
                    $backend_type = 'Libmemcached';
                } elseif ($this->is_extension_loaded('memcache')) {
                    if (isset($cache_options['memcached'])) {
                        $options = $cache_options['memcached'];
                    }
                    $enable_two_levels = true;
                    $backend_type = 'Memcached';
                }
                break;
            case 'apc':
                if ($this->is_extension_loaded('apc') && ini_get('apc.enabled')) {
                    $enable_two_levels = true;
                    $backend_type = 'Apc';
                }
                break;
            case 'xcache':
                if ($this->is_extension_loaded('xcache')) {
                    $enable_two_levels = true;
                    $backend_type = 'Xcache';
                }
                break;
            case 'eaccelerator':
            case 'varien_cache_backend_eaccelerator':
                if ($this->is_extension_loaded('eaccelerator') && ini_get('eaccelerator.enable')) {
                    $enable_two_levels = true;
                    $backend_type = Eaccelerator::class;
                }
                break;
            case 'database':
                $backend_type = Database::class;
                $options = $this->_get_db_adapter_options();
                break;
            case 'remote_synchronized_cache':
                $backend_type = Remote_Synchronized_Cache::class;
                $options['remote_backend'] = Database::class;
                $options['remote_backend_options'] = $this->_get_db_adapter_options();
                $options['local_backend'] = 'file';
                // Use cached directory operation
                if (!isset($this->cached_directories['cache'])) {
                    $cache_dir = $this->_filesystem->get_directory_write(Directory_List::CACHE);
                    $this->cached_directories['cache'] = $cache_dir->get_absolute_path();
                    $cache_dir->create();
                }
                $options['local_backend_options']['cache_dir'] = $this->cached_directories['cache'];
                break;
            default:
                // For custom backend types, use the type as-is if it's a valid class
                if ($type != $this->_default_backend && class_exists($type, true)) {
                    $backend_type = $type;
                }
        }
        if (!$backend_type) {
            $backend_type = $this->_default_backend;
            // Use cached directory operation
            if (!isset($this->cached_directories['cache'])) {
                $cache_dir = $this->_filesystem->get_directory_write(Directory_List::CACHE);
                $this->cached_directories['cache'] = $cache_dir->get_absolute_path();
                $cache_dir->create();
            }
            $this->_backend_options['cache_dir'] = $this->cached_directories['cache'];
        }
        // Merge with default backend options (optimized)
        foreach ($this->_backend_options as $option => $value) {
            if (!array_key_exists($option, $options)) {
                $options[$option] = $value;
            }
        }
        $backend_options = ['type' => $backend_type, 'options' => $options];
        if ($enable_two_levels) {
            $backend_options = $this->_get_two_levels_backend_options($backend_options, $cache_options);
        }
        return $backend_options;
    }
    /**
     * Get options for database backend type
     *
     * @return array
     */
    protected function _get_db_adapter_options()
    {
        $options['adapter_callback'] = function () {
            return $this->_resource->get_connection();
        };
        $options['data_table_callback'] = function () {
            return $this->_resource->get_table_name('cache');
        };
        $options['tags_table_callback'] = function () {
            return $this->_resource->get_table_name('cache_tag');
        };
        return $options;
    }
    /**
     * Initialize two levels backend model options
     *
     * @param array $fastOptions fast level backend type and options
     * @param array $cacheOptions all cache options
     * @return array
     */
    protected function _get_two_levels_backend_options($fast_options, $cache_options)
    {
        $options = [];
        $options['fast_backend'] = $fast_options['type'];
        $options['fast_backend_options'] = $fast_options['options'];
        $options['fast_backend_custom_naming'] = true;
        $options['fast_backend_autoload'] = true;
        $options['slow_backend_custom_naming'] = true;
        $options['slow_backend_autoload'] = true;
        if (isset($cache_options['auto_refresh_fast_cache'])) {
            $options['auto_refresh_fast_cache'] = (bool) $cache_options['auto_refresh_fast_cache'];
        } else {
            $options['auto_refresh_fast_cache'] = false;
        }
        if (isset($cache_options['slow_backend'])) {
            $options['slow_backend'] = $cache_options['slow_backend'];
        } else {
            $options['slow_backend'] = $this->_default_backend;
        }
        if (isset($cache_options['slow_backend_options'])) {
            $options['slow_backend_options'] = $cache_options['slow_backend_options'];
        } else {
            $options['slow_backend_options'] = $this->_backend_options;
        }
        if ($options['slow_backend'] == 'database') {
            $options['slow_backend'] = Database::class;
            $options['slow_backend_options'] = $this->_get_db_adapter_options();
            if (isset($cache_options['slow_backend_store_data'])) {
                $options['slow_backend_options']['store_data'] = (bool) $cache_options['slow_backend_store_data'];
            } else {
                $options['slow_backend_options']['store_data'] = false;
            }
        }
        $backend = ['type' => 'TwoLevels', 'options' => $options];
        return $backend;
    }
    /**
     * Get options of cache frontend
     *
     * @param  array $cacheOptions
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _get_frontend_options(array $cache_options)
    {
        $options = isset($cache_options['frontend_options']) ? $cache_options['frontend_options'] : [];
        if (!array_key_exists('caching', $options)) {
            $options['caching'] = true;
        }
        if (!array_key_exists('lifetime', $options)) {
            $options['lifetime'] = isset($cache_options['lifetime']) ? $cache_options['lifetime'] : self::DEFAULT_LIFETIME;
        }
        if (!array_key_exists('automatic_cleaning_factor', $options)) {
            $options['automatic_cleaning_factor'] = 0;
        }
        $options['type'] = isset($cache_options['frontend']) ? $cache_options['frontend'] : Symfony::class;
        return $options;
    }
    /**
     * Prepare and cache directory paths for cache storage
     *
     * @param array $options
     * @return void
     */
    private function prepare_cache_directories(array &$options): void
    {
        foreach (['backend_options', 'slow_backend_options'] as $section) {
            if (!empty($options[$section]['cache_dir'])) {
                $cache_dir = $options[$section]['cache_dir'];
                if (!isset($this->cached_directories[$cache_dir])) {
                    $directory = $this->_filesystem->get_directory_write(Directory_List::VAR_DIR);
                    $directory->create($cache_dir);
                    $this->cached_directories[$cache_dir] = $directory->get_absolute_path($cache_dir);
                }
                $options[$section]['cache_dir'] = $this->cached_directories[$cache_dir];
            }
        }
    }
    /**
     * Create cache frontend instance using Symfony Cache
     *
     * This method creates a Symfony-based cache adapter that implements FrontendInterface.
     * It provides PSR-6 compliant caching while maintaining full backward compatibility.
     *
     * @param array $options
     * @return FrontendInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function create_symfony_cache(array $options): Frontend_Interface
    {
        $options = $this->_get_expanded_options($options);
        // Prepare cache directories
        $this->prepare_cache_directories($options);
        // Optimize: Use cached ID prefix
        $id_prefix = $this->get_id_prefix($options);
        // Get backend configuration
        // For Symfony cache, use the original backend string from config, not the resolved Zend class
        $original_backend_type = $options['backend'] ?? $this->_default_backend;
        $backend = $this->_get_backend_options($options);
        $backend_options = $backend['options'];
        // Get default lifetime
        $frontend = $this->_get_frontend_options($options);
        $default_lifetime = $frontend['lifetime'] ?? self::DEFAULT_LIFETIME;
        // Detect if this is page cache
        $frontend_id = $options['frontend_id'] ?? null;
        $is_page_cache = in_array($frontend_id, ['page_cache', 'full_page'], true);
        // Start profiling
        $profiler_tags = ['group' => 'cache', 'operation' => 'cache:create_symfony', 'backend_type' => $original_backend_type];
        Profiler::start('cache_symfony_create', $profiler_tags);
        try {
            // Use injected adapter provider instance
            $adapter_provider = $this->adapter_provider;
            // Create cache adapter factory closure (for fork detection)
            // Use originalBackendType so SymfonyAdapterProvider can map it correctly
            $cache_factory = function () use ($adapter_provider, $original_backend_type, $backend_options, $id_prefix, $default_lifetime) {
                return $adapter_provider->create_adapter($original_backend_type, $backend_options, $id_prefix, $default_lifetime);
            };
            // Create initial cache pool
            $cache_pool = $cache_factory();
            // Create tag adapter for backend-specific operations
            $adapter = $adapter_provider->create_tag_adapter($original_backend_type, $cache_pool, $id_prefix, $is_page_cache, $backend_options);
            // Create Symfony adapter with fork detection support and tag adapter
            $result = $this->_object_manager->create(Symfony::class, ['cacheFactory' => $cache_factory, 'adapter' => $adapter, 'defaultLifetime' => $default_lifetime, 'idPrefix' => $id_prefix]);
            // Apply compression decorator if enabled in backend options
            if ($this->is_compression_enabled($backend_options)) {
                $result = $this->apply_compression_decorator($result, $backend_options);
            }
            // Apply other decorators
            $result = $this->_apply_decorators($result);
            // Apply preloading wrapper if preload_keys configured
            if (!empty($backend_options['preload_keys']) && is_array($backend_options['preload_keys'])) {
                $result = $this->_object_manager->create(Preloading_Symfony_Adapter::class, ['adapter' => $result, 'preloadKeys' => $backend_options['preload_keys']]);
            }
        } catch (\Exception $e) {
            Profiler::stop('cache_symfony_create');
            // Log the error but don't re-throw - SymfonyAdapterProvider has fallback logic
            // Re-throw exception only for critical errors (not connection failures)
            throw new \RuntimeException('Failed to create Symfony cache: ' . $e->get_message(), $e->get_code(), $e);
        }
        Profiler::stop('cache_symfony_create');
        return $result;
    }
    /**
     * Check if compression is enabled in backend options
     *
     * @param array $backendOptions
     * @return bool
     */
    private function is_compression_enabled(array $backend_options): bool
    {
        // Check if compress_data is explicitly enabled (value '1' or true)
        return isset($backend_options['compress_data']) && ($backend_options['compress_data'] === '1' || $backend_options['compress_data'] === 1);
    }
    /**
     * Apply compression decorator to cache frontend
     *
     * @param FrontendInterface $frontend
     * @param array $backendOptions
     * @return FrontendInterface
     */
    private function apply_compression_decorator(Frontend_Interface $frontend, array $backend_options): Frontend_Interface
    {
        // Get compression threshold (default: 2048 bytes)
        // Matches legacy Zend cache default of 512, but increased for better performance
        $threshold = (int) ($backend_options['compression_threshold'] ?? 2048);
        // Get compression library (default: gzip for best compatibility)
        // Supported: gzip, snappy, lzf, lz4, zstd
        $compression_lib = $backend_options['compression_lib'] ?? 'gzip';
        if (empty($compression_lib)) {
            $compression_lib = 'gzip';
            // Default to gzip if empty string
        }
        // Get compression level (1-9, default: 6)
        $compression_level = (int) ($backend_options['compression_level'] ?? 6);
        // Create and return compression decorator
        return $this->_object_manager->create(Compression_Decorator::class, ['frontend' => $frontend, 'threshold' => $threshold, 'compressionLib' => $compression_lib, 'compressionLevel' => $compression_level]);
    }
    /**
     * Check if backend is SymfonyL2Cache
     *
     * @param string $backendType
     * @return bool
     */
    private function is_symfony_l2cache(string $backend_type): bool
    {
        $backend_lower = strtolower($backend_type);
        // Check for symfony_l2 or l2_symfony or SymfonyL2Cache
        return in_array($backend_lower, ['symfony_l2', 'l2_symfony', 'symfony_l2_cache', 'magento\framework\cache\backend\symfonyl2cache'], true);
    }
    /**
     * Create SymfonyL2Cache (Clean L2 cache for Symfony)
     *
     * @param array $options
     * @return FrontendInterface
     * @throws \Exception
     */
    private function create_symfony_l2cache(array $options): Frontend_Interface
    {
        $backend_options = $options['backend_options'] ?? [];
        // Get remote backend configuration (L2 - persistent, shared)
        $remote_backend = $backend_options['remote_backend'] ?? 'redis';
        $remote_backend_options = $backend_options['remote_backend_options'] ?? [];
        // Get local backend configuration (L1 - fast, local)
        $local_backend = $backend_options['local_backend'] ?? 'file';
        $local_backend_options = $backend_options['local_backend_options'] ?? [];
        // Get common options
        $frontend = $this->_get_frontend_options($options);
        $default_lifetime = $frontend['lifetime'] ?? self::DEFAULT_LIFETIME;
        Profiler::start('cache_symfony_l2_create', ['group' => 'cache', 'operation' => 'cache:create_symfony_l2', 'remote_backend' => $remote_backend, 'local_backend' => $local_backend]);
        try {
            // Create remote backend (L2 - Symfony)
            $remote_options = array_merge($options, ['backend' => $remote_backend, 'backend_options' => $remote_backend_options]);
            $remote_frontend = $this->create_symfony_cache($remote_options);
            // Create local backend (L1 - Symfony)
            $local_options = array_merge($options, ['backend' => $local_backend, 'backend_options' => $local_backend_options]);
            $local_frontend = $this->create_symfony_cache($local_options);
            // Create SymfonyL2Cache backend
            $l2Backend = $this->_object_manager->create(\Magento\Framework\Cache\Backend\Symfony_L2cache::class, ['remote' => $remote_frontend, 'local' => $local_frontend, 'options' => ['cleanup_percentage' => $backend_options['cleanup_percentage'] ?? 90, 'use_stale_cache' => $backend_options['use_stale_cache'] ?? false]]);
            // Wrap in frontend adapter
            $result = $this->_object_manager->create(\Magento\Framework\Cache\Frontend\Adapter\Remote_Synchronized_Symfony_Adapter::class, ['backend' => $l2Backend, 'defaultLifetime' => $default_lifetime]);
            Profiler::stop('cache_symfony_l2_create');
            return $result;
        } catch (\Exception $e) {
            Profiler::stop('cache_symfony_l2_create');
            throw new \RuntimeException('Failed to create Symfony L2 cache: ' . $e->get_message(), $e->get_code(), $e);
        }
    }
    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}