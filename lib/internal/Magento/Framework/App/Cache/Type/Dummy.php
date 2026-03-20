<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\App\Cache_Interface;
/**
 * Dummy cache adapter
 *
 * for cases when need to disable interaction with cache
 * but no specific cache type is used
 */
class Dummy implements Cache_Interface
{
    /**
     * Required by CacheInterface
     *
     * @return null
     */
    public function get_frontend()
    {
        return null;
    }
    /**
     * Pretend to load data from cache by id
     *
     * {@inheritdoc}
     */
    public function load($identifier)
    {
        return null;
    }
    /**
     * Pretend to save data
     *
     * {@inheritdoc}
     */
    public function save($data, $identifier, $tags = [], $life_time = null)
    {
        return false;
    }
    /**
     * Pretend to remove cached data by identifier
     *
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        return true;
    }
    /**
     * Pretend to clean cached data by specific tag
     *
     * {@inheritdoc}
     */
    public function clean($tags = [])
    {
        return true;
    }
}