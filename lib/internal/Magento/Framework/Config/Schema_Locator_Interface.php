<?php

declare (strict_types=1);
/**
 * Configuration validation schema locator
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * Config schema locator interface.
 *
 * @api
 * @since 100.0.2
 */
interface Schema_Locator_Interface
{
    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function get_schema();
    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function get_per_file_schema();
}