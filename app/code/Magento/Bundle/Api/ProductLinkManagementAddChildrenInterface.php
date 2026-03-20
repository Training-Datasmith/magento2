<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Api;

/**
 * Interface for Bulk children addition
 */
interface Product_Link_Management_Add_Children_Interface
{
    /**
     * Bulk add children operation
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $optionId
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $linkedProducts
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return void
     */
    public function add_children(\Magento\Catalog\Api\Data\Product_Interface $product, int $option_id, array $linked_products);
}