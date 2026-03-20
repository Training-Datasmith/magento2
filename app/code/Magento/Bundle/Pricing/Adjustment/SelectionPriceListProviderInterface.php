<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Catalog\Model\Product;
/**
 * Provide list of bundle selection prices
 * @api
 * @since 100.2.0
 */
interface Selection_Price_List_Provider_Interface
{
    /**
     * @param Product $bundleProduct
     * @param boolean $searchMin
     * @param boolean $useRegularPrice
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     * @since 100.2.0
     */
    public function get_price_list(Product $bundle_product, $search_min, $use_regular_price);
}