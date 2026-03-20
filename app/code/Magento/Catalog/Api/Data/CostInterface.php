<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Cost interface.
 * @api
 * @since 102.0.0
 */
interface Cost_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**#@+
     * Constants
     */
    public const COST = 'cost';
    public const STORE_ID = 'store_id';
    public const SKU = 'sku';
    /**#@-*/
    /**
     * Set cost value.
     *
     * @param float $cost
     * @return $this
     * @since 102.0.0
     */
    public function set_cost($cost);
    /**
     * Get cost value.
     *
     * @return float
     * @since 102.0.0
     */
    public function get_cost();
    /**
     * Set store id.
     *
     * @param int $storeId
     * @return $this
     * @since 102.0.0
     */
    public function set_store_id($store_id);
    /**
     * Get store id.
     *
     * @return int
     * @since 102.0.0
     */
    public function get_store_id();
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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\CostExtensionInterface|null
     * @since 102.0.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CostExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Cost_Extension_Interface $extension_attributes);
}