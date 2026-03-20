<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Tier price interface.
 * @api
 * @since 102.0.0
 */
interface Tier_Price_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**#@+
     * Constants
     */
    public const PRICE = 'price';
    public const PRICE_TYPE = 'price_type';
    public const WEBSITE_ID = 'website_id';
    public const SKU = 'sku';
    public const CUSTOMER_GROUP = 'customer_group';
    public const QUANTITY = 'quantity';
    public const PRICE_TYPE_FIXED = 'fixed';
    public const PRICE_TYPE_DISCOUNT = 'discount';
    /**#@-*/
    /**
     * Set tier price.
     *
     * @param float $price
     * @return $this
     * @since 102.0.0
     */
    public function set_price($price);
    /**
     * Get tier price.
     *
     * @return float
     * @since 102.0.0
     */
    public function get_price();
    /**
     * Set tier price type.
     *
     * @param string $type
     * @return $this
     * @since 102.0.0
     */
    public function set_price_type($type);
    /**
     * Get tier price type.
     *
     * @return string
     * @since 102.0.0
     */
    public function get_price_type();
    /**
     * Set website id.
     *
     * @param int $websiteId
     * @return $this
     * @since 102.0.0
     */
    public function set_website_id($website_id);
    /**
     * Get website id.
     *
     * @return int
     * @since 102.0.0
     */
    public function get_website_id();
    /**
     * Set SKU.
     *
     * @param string $sku
     * @return $this
     * @since 102.0.0
     */
    public function set_sku($sku);
    /**
     * Get SKU.
     *
     * @return string
     * @since 102.0.0
     */
    public function get_sku();
    /**
     * Set customer group.
     *
     * @param string $group
     * @return $this
     * @since 102.0.0
     */
    public function set_customer_group($group);
    /**
     * Get customer group.
     *
     * @return string
     * @since 102.0.0
     */
    public function get_customer_group();
    /**
     * Set quantity.
     *
     * @param float $quantity
     * @return $this
     * @since 102.0.0
     */
    public function set_quantity($quantity);
    /**
     * Get quantity.
     *
     * @return float
     * @since 102.0.0
     */
    public function get_quantity();
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\TierPriceExtensionInterface|null
     * @since 102.0.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Tier_Price_Extension_Interface $extension_attributes);
}