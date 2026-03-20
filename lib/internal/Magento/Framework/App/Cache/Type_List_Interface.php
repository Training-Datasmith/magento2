<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache;

/**
 * @api
 * @since 100.0.2
 */
interface Type_List_Interface
{
    /**
     * Get information about all declared cache types
     *
     * @return array
     */
    public function get_types();
    /**
     * Get label information of available cache types
     *
     * @return array
     */
    public function get_type_labels();
    /**
     * Get array of all invalidated cache types
     *
     * @return array
     */
    public function get_invalidated();
    /**
     * Mark specific cache type(s) as invalidated
     *
     * @param string|array $typeCode
     * @return void
     */
    public function invalidate($type_code);
    /**
     * Clean cached data for specific cache type
     *
     * @param string $typeCode
     * @return void
     */
    public function clean_type($type_code);
}