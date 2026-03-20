<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Render;

use Magento\Bundle\Pricing\Price\Final_Price;
use Magento\Catalog\Pricing\Price\Custom_Option_Price;
use Magento\Catalog\Pricing\Render as CatalogRender;
/**
 * Class for final_price rendering
 */
class Final_Price_Box extends Catalog_Render\Final_Price_Box
{
    /**
     * Check if bundle product has one or more options, or custom options, with different prices
     *
     * @return bool
     */
    public function show_range_price()
    {
        /** @var FinalPrice $bundlePrice */
        $bundle_price = $this->get_price_type(Final_Price::PRICE_CODE);
        $show_range = $bundle_price->get_minimal_price() != $bundle_price->get_maximal_price();
        if (!$show_range) {
            //Check the custom options, if any
            /** @var \Magento\Catalog\Pricing\Price\CustomOptionPrice $customOptionPrice */
            $custom_option_price = $this->get_price_type(Custom_Option_Price::PRICE_CODE);
            $show_range = $custom_option_price->get_custom_option_range(true) != $custom_option_price->get_custom_option_range(false);
        }
        return $show_range;
    }
}