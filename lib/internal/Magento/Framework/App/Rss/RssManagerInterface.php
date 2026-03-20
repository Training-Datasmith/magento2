<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Rss;

/**
 * Interface RssManagerInterface
 *
 * @api
 */
interface Rss_Manager_Interface
{
    /**
     * Get Data Provider by type
     * @param string $type
     * @return DataProviderInterface
     */
    public function get_provider($type);
    /**
     * Get all registered providers
     *
     * @return array
     */
    public function get_providers();
}