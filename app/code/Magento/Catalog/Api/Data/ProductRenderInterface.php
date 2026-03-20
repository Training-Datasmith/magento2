<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Catalog\Api\Data\Product_Render\Button_Interface;
use Magento\Catalog\Api\Data\Product_Render\Price_Info_Interface;
use Magento\Framework\Api\Extensible_Data_Interface;
/**
 * Represents Data Object which holds enough information to render product
 * This information is put into part as Add To Cart or Add to Compare Data or Price Data
 *
 * @api
 * @since 102.0.0
 */
interface Product_Render_Interface extends Extensible_Data_Interface
{
    /**
     * Provide information needed for render "Add To Cart" button on front
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ButtonInterface
     * @since 102.0.0
     */
    public function get_add_to_cart_button();
    /**
     * Set information needed for render "Add To Cart" button on front
     *
     * @param ButtonInterface $cartAddToCartButton
     * @return void
     * @since 102.0.0
     */
    public function set_add_to_cart_button(Button_Interface $cart_add_to_cart_button);
    /**
     * Provide information needed for render "Add To Compare" button on front
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ButtonInterface
     * @since 102.0.0
     */
    public function get_add_to_compare_button();
    /**
     * Set information needed for render "Add To Compare" button on front
     *
     * @param ButtonInterface $compareButton
     * @return string
     * @since 102.0.0
     */
    public function set_add_to_compare_button(Button_Interface $compare_button);
    /**
     * Provide information needed for render prices and adjustments for different product types on front
     *
     * Prices are represented in raw format and in current currency
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface
     * @since 102.0.0
     */
    public function get_price_info();
    /**
     * Set information needed for render prices and adjustments for different product types on front
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface $priceInfo
     * @return void
     * @since 102.0.0
     */
    public function set_price_info(Price_Info_Interface $price_info);
    /**
     * Provide enough information, that needed to render image on front
     *
     * Images can be separated by image codes
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ImageInterface[]
     * @since 102.0.0
     */
    public function get_images();
    /**
     * Set enough information, that needed to render image on front
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\ImageInterface[] $images
     * @return void
     * @since 102.0.0
     */
    public function set_images(array $images);
    /**
     * Provide product url
     *
     * @return string
     * @since 102.0.0
     */
    public function get_url();
    /**
     * Set product url
     *
     * @param string $url
     * @return void
     * @since 102.0.0
     */
    public function set_url($url);
    /**
     * Provide product identifier
     *
     * @return int
     * @since 102.0.0
     */
    public function get_id();
    /**
     * Set product identifier
     *
     * @param int $id
     * @return void
     * @since 102.0.0
     */
    public function set_id($id);
    /**
     * Provide product name
     *
     * @return string
     * @since 102.0.0
     */
    public function get_name();
    /**
     * Set product name
     *
     * @param string $name
     * @return void
     * @since 102.0.0
     */
    public function set_name($name);
    /**
     * Provide product type. Such as bundle, grouped, simple, etc...
     *
     * @return string
     * @since 102.0.0
     */
    public function get_type();
    /**
     * Set product type.
     *
     * @param string $productType
     * @return void
     * @since 102.0.0
     */
    public function set_type($product_type);
    /**
     * Provide information about product saleability (In Stock)
     *
     * @return string
     * @since 102.0.0
     */
    public function get_is_salable();
    /**
     * Set information about product saleability (Stock, other conditions)
     *
     * Is used to provide information to frontend JS renders
     * You can add plugin, in order to hide product on product page or product list on front
     *
     * @param string $isSalable
     * @return void
     * @since 102.0.0
     */
    public function set_is_salable($is_salable);
    /**
     * Provide information about current store id or requested store id
     *
     * Product should be assigned to provided store id
     * This setting affect store scope attributes
     *
     * @return int
     * @since 102.0.0
     */
    public function get_store_id();
    /**
     * Set current or desired store id to product
     *
     * @param int $storeId
     * @return void
     * @since 102.0.0
     */
    public function set_store_id($store_id);
    /**
     * Provide current or desired currency code to product
     *
     * This setting affect formatted prices*
     *
     * @return string
     * @since 102.0.0
     */
    public function get_currency_code();
    /**
     * Set current or desired currency code to product
     *
     * @param string $currencyCode
     * @return void
     * @since 102.0.0
     */
    public function set_currency_code($currency_code);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRenderExtensionInterface
     * @since 102.0.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRenderExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Render_Extension_Interface $extension_attributes);
}