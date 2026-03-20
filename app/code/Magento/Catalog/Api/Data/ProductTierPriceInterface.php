<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\Extensible_Data_Interface;
/**
 * @api
 * @since 100.0.2
 */
interface Product_Tier_Price_Interface extends Extensible_Data_Interface
{
    public const QTY = 'qty';
    public const VALUE = 'value';
    public const CUSTOMER_GROUP_ID = 'customer_group_id';
    /**
     * Retrieve customer group id
     *
     * @return int
     */
    public function get_customer_group_id();
    /**
     * Set customer group id
     *
     * @param int $customerGroupId
     * @return $this
     */
    public function set_customer_group_id($customer_group_id);
    /**
     * Retrieve tier qty
     *
     * @return float
     */
    public function get_qty();
    /**
     * Set tier qty
     *
     * @param float $qty
     * @return $this
     */
    public function set_qty($qty);
    /**
     * Retrieve price value
     *
     * @return float
     */
    public function get_value();
    /**
     * Set price value
     *
     * @param float $value
     * @return $this
     */
    public function set_value($value);
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Tier_Price_Extension_Interface $extension_attributes);
}