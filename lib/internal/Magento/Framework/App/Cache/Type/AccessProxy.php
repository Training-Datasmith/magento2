<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
/**
 * Proxy that delegates execution to an original cache type instance, if access is allowed at the moment.
 * It's typical for "access proxies" to have a decorator-like implementation, the difference is logical -
 * controlling access rather than attaching additional responsibility to a subject.
 */
namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\Cache\Cache_Constants;
class Access_Proxy extends \Magento\Framework\Cache\Frontend\Decorator\Bare
{
    /**
     * Cache types manager
     *
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $_cache_state;
    /**
     * Cache type identifier
     *
     * @var string
     */
    private $_identifier;
    /**
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param string $identifier Cache type identifier
     */
    public function __construct(\Magento\Framework\Cache\Frontend_Interface $frontend, \Magento\Framework\App\Cache\State_Interface $cache_state, $identifier)
    {
        parent::__construct($frontend);
        $this->_cache_state = $cache_state;
        $this->_identifier = $identifier;
    }
    /**
     * Whether a cache type is enabled at the moment or not
     *
     * @return bool
     */
    protected function _is_enabled()
    {
        return $this->_cache_state->is_enabled($this->_identifier);
    }
    /**
     * @inheritDoc
     */
    public function test($identifier)
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        return parent::test($identifier);
    }
    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        return parent::load($identifier);
    }
    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $life_time = null)
    {
        if (!$this->_is_enabled()) {
            return true;
        }
        return parent::save($data, $identifier, $tags, $life_time);
    }
    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        if (!$this->_is_enabled()) {
            return true;
        }
        return parent::remove($identifier);
    }
    /**
     * @inheritDoc
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, array $tags = [])
    {
        if (!$this->_is_enabled()) {
            return true;
        }
        return parent::clean($mode, $tags);
    }
}