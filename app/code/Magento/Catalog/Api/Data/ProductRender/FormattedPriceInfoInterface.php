<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data\Product_Render;

/**
 * Formatted Price interface.
 *
 * Aggregate formatted html with price representations.
 * E.g.:
 * <span class="price">$9.00</span>
 * Consider currency, rounding and html
 *
 * @api
 * @since 102.0.0
 */
interface Formatted_Price_Info_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**
     * Retrieve html with final price
     *
     * @return string
     * @since 102.0.0
     */
    public function get_final_price();
    /**
     * Set the final price: usually it calculated as minimal price of the product
     *
     * Can be different depends on type of product
     *
     * @param string $finalPrice
     * @return void
     * @since 102.0.0
     */
    public function set_final_price($final_price);
    /**
     * Retrieve max price of a product
     *
     * E.g. for product with custom options is price with the most expensive custom option
     *
     * @return string
     * @since 102.0.0
     */
    public function get_max_price();
    /**
     * Set the max price of the product
     *
     * @param string $maxPrice
     * @return void
     * @since 102.0.0
     */
    public function set_max_price($max_price);
    /**
     * Retrieve the minimal price of the product or variation
     *
     * The minimal price is for example, the lowest price of all variations for complex product
     *
     * @return string
     * @since 102.0.0
     */
    public function get_minimal_price();
    /**
     * Set max regular price
     * Max regular price is the same, as maximum price, except of excluding calculating special price and catalog rules
     * in it
     *
     * @param string $maxRegularPrice
     * @return void
     * @since 102.0.0
     */
    public function set_max_regular_price($max_regular_price);
    /**
     * Retrieve max regular price
     *
     * @return string
     * @since 102.0.0
     */
    public function get_max_regular_price();
    /**
     * The minimal regular price has the same behavior of calculation as max regular price, but is opposite price
     *
     * @param string $minRegularPrice
     * @return void
     * @since 102.0.0
     */
    public function set_minimal_regular_price($min_regular_price);
    /**
     * Retrieve minimal regular price
     *
     * @return string
     * @since 102.0.0
     */
    public function get_minimal_regular_price();
    /**
     * Set special price
     *
     * Special price - is temporary price, that can be set to specific product
     *
     * @param string $specialPrice
     * @return void
     * @since 102.0.0
     */
    public function set_special_price($special_price);
    /**
     * Retrieve special price
     *
     * @return string
     * @since 102.0.0
     */
    public function get_special_price();
    /**
     * Set minimal price
     *
     * @param string $minimalPrice
     * @return void
     * @since 102.0.0
     */
    public function set_minimal_price($minimal_price);
    /**
     * Regular price - is price of product without discounts and special price with taxes and fixed product tax
     *
     * Usually this price is corresponding to price in admin panel of product
     *
     * @return string
     * @since 102.0.0
     */
    public function get_regular_price();
    /**
     * Set regular price
     *
     * @param string $regularPrice
     * @return void
     * @since 102.0.0
     */
    public function set_regular_price($regular_price);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoExtensionInterface|null
     * @since 102.0.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Render\Formatted_Price_Info_Extension_Interface $extension_attributes);
}