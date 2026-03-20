<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Magento application product metadata
 *
 * @api
 * @since 100.0.2
 */
interface Product_Metadata_Interface
{
    /**
     * Get Product version
     *
     * @return string
     */
    public function get_version();
    /**
     * Get Product edition
     *
     * @return string
     */
    public function get_edition();
    /**
     * Get Product name
     *
     * @return string
     */
    public function get_name();
}