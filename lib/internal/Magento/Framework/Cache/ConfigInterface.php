<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Cache;

/**
 * Interface \Magento\Framework\Cache\ConfigInterface
 *
 * @api
 */
interface Config_Interface
{
    /**
     * Get configuration of all cache types
     *
     * @return array
     */
    public function get_types();
    /**
     * Get configuration of specified cache type
     *
     * @param string $type
     * @return array
     */
    public function get_type($type);
}