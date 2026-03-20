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
interface Category_Link_Repository_Interface
{
    /**
     * Assign a product to the required category
     *
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink
     * @return bool will returned True if assigned
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function save(\Magento\Catalog\Api\Data\Category_Product_Link_Interface $product_link);
    /**
     * Remove the product assignment from the category
     *
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink
     * @return bool will returned True if products successfully deleted
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function delete(\Magento\Catalog\Api\Data\Category_Product_Link_Interface $product_link);
    /**
     * Remove the product assignment from the category by category id and sku
     *
     * @param int $categoryId
     * @param string $sku
     * @return bool will returned True if products successfully deleted
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function delete_by_ids($category_id, $sku);
}