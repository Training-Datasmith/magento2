<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Dto that holds render information about products
 *
 * @api
 */
interface Product_Render_Search_Results_Interface
{
    /**
     * Get list of products rendered information
     *
     * @return \Magento\Catalog\Api\Data\ProductRenderInterface[]
     */
    public function get_items();
    /**
     * Set list of products rendered information
     *
     * @param \Magento\Catalog\Api\Data\ProductRenderInterface[] $items
     * @return $this
     */
    public function set_items(array $items);
}