<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Frontend;

use Magento\Framework\App\Cache\Type\Frontend_Pool;
use Magento\Framework\App\Deployment_Config;
/**
 * In-memory readonly pool of all cache front-end instances known to the system
 */
class Pool implements \Iterator
{
    /**
     * Frontend identifier associated with the default settings
     */
    public const DEFAULT_FRONTEND_ID = 'default';
    /**
     * @var DeploymentConfig
     */
    private $deployment_config;
    /**
     * @var Factory
     */
    private $_factory;
    /**
     * @var \Magento\Framework\Cache\FrontendInterface[]
     */
    private $_instances;
    /**
     * @var array
     */
    private $_frontend_settings;
    /**
     * @param DeploymentConfig $deploymentConfig
     * @param Factory $frontendFactory
     * @param array $frontendSettings Format: array('<frontend_id>' => array(<cache_settings>), ...)
     */
    public function __construct(Deployment_Config $deployment_config, Factory $frontend_factory, array $frontend_settings = [])
    {
        $this->deployment_config = $deployment_config;
        $this->_factory = $frontend_factory;
        $this->_frontend_settings = $frontend_settings + [self::DEFAULT_FRONTEND_ID => []];
    }
    /**
     * Create instances of every cache frontend known to the system.
     *
     * Method is to be used for delayed initialization of the iterator.
     *
     * @return void
     */
    protected function _initialize()
    {
        if ($this->_instances === null) {
            $this->_instances = [];
            foreach ($this->_get_cache_settings() as $frontend_id => $frontend_options) {
                // Pass frontend ID to factory for cache type detection (e.g., FPC vs application cache)
                $frontend_options['frontend_id'] = $frontend_id;
                $this->_instances[$frontend_id] = $this->_factory->create($frontend_options);
            }
        }
    }
    /**
     * Retrieve settings for all cache front-ends known to the system
     *
     * @return array Format: array('<frontend_id>' => array(<cache_settings>), ...)
     */
    protected function _get_cache_settings()
    {
        /*
         * Merging is intentionally implemented through array_replace_recursive() instead of array_merge(), because even
         * though some settings may become irrelevant when the cache storage type is changed, they don't do any harm
         * and can be overwritten when needed.
         * Also array_merge leads to unexpected behavior, for for example by dropping the
         * default cache_dir setting from di.xml when a cache id_prefix is configured in app/etc/env.php.
         */
        $cache_info = $this->deployment_config->get_config_data(Frontend_Pool::KEY_CACHE);
        if (null !== $cache_info && array_key_exists(Frontend_Pool::KEY_FRONTEND_CACHE, $cache_info)) {
            return array_replace_recursive($this->_frontend_settings, $cache_info[Frontend_Pool::KEY_FRONTEND_CACHE]);
        }
        return $this->_frontend_settings;
    }
    /**
     * @inheritdoc
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    #[\Return_Type_Will_Change]
    public function current()
    {
        $this->_initialize();
        return current($this->_instances);
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function key()
    {
        $this->_initialize();
        return key($this->_instances);
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function next()
    {
        $this->_initialize();
        next($this->_instances);
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function rewind()
    {
        $this->_initialize();
        reset($this->_instances);
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function valid()
    {
        $this->_initialize();
        return (bool) current($this->_instances);
    }
    /**
     * Retrieve frontend instance by its unique identifier
     *
     * @param string $identifier Cache frontend identifier
     * @return \Magento\Framework\Cache\FrontendInterface Cache frontend instance
     * @throws \InvalidArgumentException
     */
    public function get($identifier)
    {
        $this->_initialize();
        if (isset($this->_instances[$identifier])) {
            return $this->_instances[$identifier];
        }
        if (!isset($this->_instances[self::DEFAULT_FRONTEND_ID])) {
            throw new \InvalidArgumentException("Cache frontend '{$identifier}' is not recognized. As well as " . self::DEFAULT_FRONTEND_ID . 'cache is not configured');
        }
        return $this->_instances[self::DEFAULT_FRONTEND_ID];
    }
}