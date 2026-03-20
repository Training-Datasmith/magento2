<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\Backend\Extended_Backend_Interface;
use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Frontend_Interface;
/**
 * Frontend adapter for RemoteSynchronizedCache with Symfony backends
 *
 * This adapter implements FrontendInterface and wraps a RemoteSynchronizedCache backend,
 * allowing L2 cache to work seamlessly with Symfony cache backends.
 */
class Remote_Synchronized_Symfony_Adapter implements Frontend_Interface
{
    /**
     * @var ExtendedBackendInterface
     */
    private Extended_Backend_Interface $backend;
    /**
     * @var int
     */
    private int $default_lifetime;
    /**
     * Constructor
     *
     * @param ExtendedBackendInterface $backend RemoteSynchronizedCache backend
     * @param int $defaultLifetime Default cache lifetime
     */
    public function __construct(Extended_Backend_Interface $backend, int $default_lifetime = 7200)
    {
        $this->backend = $backend;
        $this->default_lifetime = $default_lifetime;
    }
    /**
     * @inheritDoc
     */
    public function test($identifier)
    {
        return $this->backend->test($identifier);
    }
    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        return $this->backend->load($identifier);
    }
    /**
     * @inheritDoc
     */
    public function save($data, $identifier, $tags = [], $life_time = null)
    {
        $lifetime = $life_time ?? $this->default_lifetime;
        return $this->backend->save($data, $identifier, $tags, $lifetime);
    }
    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        return $this->backend->remove($identifier);
    }
    /**
     * @inheritDoc
     */
    public function clean($mode = Cache_Constants::CLEANING_MODE_ALL, $tags = [])
    {
        return $this->backend->clean($mode, $tags);
    }
    /**
     * Get the underlying backend
     *
     * @return ExtendedBackendInterface
     */
    public function get_backend()
    {
        return $this->backend;
    }
    /**
     * Get low-level frontend (for backward compatibility)
     *
     * @return mixed
     */
    public function get_low_level_frontend()
    {
        // Return self as we are the frontend
        return $this;
    }
}