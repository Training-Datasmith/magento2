<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\Cache_Constants;
/**
 * Cache frontend decorator that performs profiling of cache operations
 */
class Profiler extends \Magento\Framework\Cache\Frontend\Decorator\Bare
{
    /**
     * Backend class prefixes to be striped from profiler tags
     *
     * @var string[]
     */
    private $_backend_prefixes = [];
    /**
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @param string[] $backendPrefixes Backend class prefixes to be striped for profiling informativeness
     */
    public function __construct(\Magento\Framework\Cache\Frontend_Interface $frontend, $backend_prefixes = [])
    {
        parent::__construct($frontend);
        $this->_backend_prefixes = $backend_prefixes;
    }
    /**
     * Retrieve profiler tags that correspond to a cache operation
     *
     * @param string $operation
     * @return array
     */
    protected function _get_profiler_tags($operation)
    {
        return ['group' => 'cache', 'operation' => 'cache:' . $operation, 'frontend_type' => get_class($this->get_low_level_frontend()), 'backend_type' => $this->_get_backend_type()];
    }
    /**
     * Get short cache backend type name by striping known backend class prefixes
     *
     * @return string
     */
    protected function _get_backend_type()
    {
        $result = get_class($this->get_backend());
        foreach ($this->_backend_prefixes as $backend_class_prefix) {
            if (substr($result, 0, strlen($backend_class_prefix)) == $backend_class_prefix) {
                $result = substr($result, strlen($backend_class_prefix));
                break;
            }
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function test($identifier)
    {
        \Magento\Framework\Profiler::start('cache_test', $this->_get_profiler_tags('test'));
        $result = parent::test($identifier);
        \Magento\Framework\Profiler::stop('cache_test');
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        \Magento\Framework\Profiler::start('cache_load', $this->_get_profiler_tags('load'));
        $result = parent::load($identifier);
        \Magento\Framework\Profiler::stop('cache_load');
        return $result;
    }
    /**
     * @inheritDoc
     *
     * Enforce marking with a tag
     */
    public function save($data, $identifier, array $tags = [], $life_time = null)
    {
        \Magento\Framework\Profiler::start('cache_save', $this->_get_profiler_tags('save'));
        $result = parent::save($data, $identifier, $tags, $life_time);
        \Magento\Framework\Profiler::stop('cache_save');
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        \Magento\Framework\Profiler::start('cache_remove', $this->_get_profiler_tags('remove'));
        $result = parent::remove($identifier);
        \Magento\Framework\Profiler::stop('cache_remove');
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, array $tags = [])
    {
        \Magento\Framework\Profiler::start('cache_clean', $this->_get_profiler_tags('clean'));
        $result = parent::clean($mode, $tags);
        \Magento\Framework\Profiler::stop('cache_clean');
        return $result;
    }
}