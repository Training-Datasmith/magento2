<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Product Special Price Interface is used to encapsulate data that can be processed by efficient price API.
 * @api
 * @since 102.0.0
 */
interface Special_Price_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**#@+
     * Constants
     */
    public const PRICE = 'price';
    public const STORE_ID = 'store_id';
    public const SKU = 'sku';
    public const PRICE_FROM = 'price_from';
    public const PRICE_TO = 'price_to';
    /**#@-*/
    /**
     * Set product special price value.
     *
     * @param float $price
     * @return $this
     * @since 102.0.0
     */
    public function set_price($price);
    /**
     * Get product special price value.
     *
     * @return float
     * @since 102.0.0
     */
    public function get_price();
    /**
     * Set ID of store, that contains special price value.
     *
     * @param int $storeId
     * @return $this
     * @since 102.0.0
     */
    public function set_store_id($store_id);
    /**
     * Get ID of store, that contains special price value.
     *
     * @return int
     * @since 102.0.0
     */
    public function get_store_id();
    /**
     * Set SKU of product, that contains special price value.
     *
     * @param string $sku
     * @return $this
     * @since 102.0.0
     */
    public function set_sku($sku);
    /**
     * Get SKU of product, that contains special price value.
     *
     * @return string
     * @since 102.0.0
     */
    public function get_sku();
    /**
     * Set start date for special price in Y-m-d H:i:s format.
     *
     * @param string $datetime
     * @return $this
     * @since 102.0.0
     */
    public function set_price_from($datetime);
    /**
     * Get start date for special price in Y-m-d H:i:s format.
     *
     * @return string
     * @since 102.0.0
     */
    public function get_price_from();
    /**
     * Set end date for special price in Y-m-d H:i:s format.
     *
     * @param string $datetime
     * @return $this
     * @since 102.0.0
     */
    public function set_price_to($datetime);
    /**
     * Get end date for special price in Y-m-d H:i:s format.
     *
     * @return string
     * @since 102.0.0
     */
    public function get_price_to();
    /**
     * Retrieve existing extension attributes object.
     * If extension attributes do not exist return null.
     *
     * @return \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface|null
     * @since 102.0.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Special_Price_Extension_Interface $extension_attributes);
}