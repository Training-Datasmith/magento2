<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Api;

/**
 * Interface for Management of ProductLink
 * @api
 * @since 100.0.2
 */
interface Product_Link_Management_Interface
{
    /**
     * Get all children for Bundle product
     *
     * @param string $productSku
     * @param int $optionId
     * @return \Magento\Bundle\Api\Data\LinkInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get_children($product_sku, $option_id = null);
    /**
     * Add child product to specified Bundle option by product sku
     *
     * @param string $sku
     * @param int $optionId
     * @param \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return int
     */
    public function add_child_by_product_sku($sku, $option_id, \Magento\Bundle\Api\Data\Link_Interface $linked_product);
    /**
     * @param string $sku
     * @param \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     */
    public function save_child($sku, \Magento\Bundle\Api\Data\Link_Interface $linked_product);
    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $optionId
     * @param \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return int
     */
    public function add_child(\Magento\Catalog\Api\Data\Product_Interface $product, $option_id, \Magento\Bundle\Api\Data\Link_Interface $linked_product);
    /**
     * Remove product from Bundle product option
     *
     * @param string $sku
     * @param int $optionId
     * @param string $childSku
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     */
    public function remove_child($sku, $option_id, $child_sku);
}