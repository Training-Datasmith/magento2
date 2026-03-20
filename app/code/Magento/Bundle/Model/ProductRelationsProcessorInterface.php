<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model;

/**
 * Processor to handle bundle product relations.
 */
interface Product_Relations_Processor_Interface
{
    /**
     * Process bundle product relations.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $existingProductOptions
     * @param array $expectedProductOptions
     * @return void
     */
    public function process(\Magento\Catalog\Api\Data\Product_Interface $product, array $existing_product_options, array $expected_product_options): void;
}