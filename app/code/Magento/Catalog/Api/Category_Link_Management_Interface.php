<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 * @since 100.0.2
 */
interface Category_Link_Management_Interface
{
    /**
     * Get products assigned to category
     *
     * @param int $categoryId
     * @return \Magento\Catalog\Api\Data\CategoryProductLinkInterface[]
     */
    public function get_assigned_products($category_id);
    /**
     * Assign product to given categories
     *
     * @param string $productSku
     * @param int[] $categoryIds
     * @return bool
     * @since 101.0.0
     */
    public function assign_product_to_categories($product_sku, array $category_ids);
}