<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface Product_Website_Link_Interface
{
    /**
     * @return string
     */
    public function get_sku();
    /**
     * @param string $sku
     * @return $this
     */
    public function set_sku($sku);
    /**
     * Get website ids
     *
     * @return int
     */
    public function get_website_id();
    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function set_website_id($website_id);
}