<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Rss;

/**
 * @api
 * @since 100.0.2
 */
interface Data_Provider_Interface
{
    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     */
    public function is_allowed();
    /**
     * Get RSS feed items
     *
     * @return array
     */
    public function get_rss_data();
    /**
     * @return string
     */
    public function get_cache_key();
    /**
     * @return int
     */
    public function get_cache_lifetime();
    /**
     * Get information about all feeds this Data Provider is responsible for
     *
     * @return array
     */
    public function get_feeds();
    /**
     * @return bool
     */
    public function is_auth_required();
}