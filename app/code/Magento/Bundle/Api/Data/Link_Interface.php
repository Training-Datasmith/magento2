<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Api\Data;

/**
 * Interface LinkInterface
 * @api
 * @since 100.0.2
 */
interface Link_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    public const PRICE_TYPE_FIXED = 0;
    public const PRICE_TYPE_PERCENT = 1;
    /**
     * Get the identifier
     *
     * @return string|null
     */
    public function get_id();
    /**
     * Set id
     *
     * @param string $id
     * @return $this
     */
    public function set_id($id);
    /**
     * Get linked product sku
     *
     * @return string|null
     */
    public function get_sku();
    /**
     * Set linked product sku
     *
     * @param string $sku
     * @return $this
     */
    public function set_sku($sku);
    /**
     * Get option id
     *
     * @return int|null
     */
    public function get_option_id();
    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function set_option_id($option_id);
    /**
     * Get qty
     *
     * @return float|null
     */
    public function get_qty();
    /**
     * Set qty
     *
     * @param float $qty
     * @return $this
     */
    public function set_qty($qty);
    /**
     * Get position
     *
     * @return int|null
     */
    public function get_position();
    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position);
    /**
     * Get is default
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_is_default();
    /**
     * Set is default
     *
     * @param bool $isDefault
     * @return $this
     */
    public function set_is_default($is_default);
    /**
     * Get price
     *
     * @return float
     */
    public function get_price();
    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function set_price($price);
    /**
     * Get price type
     *
     * @return int
     */
    public function get_price_type();
    /**
     * Set price type
     *
     * @param int $priceType
     * @return $this
     */
    public function set_price_type($price_type);
    /**
     * Get whether quantity could be changed
     *
     * @return int|null
     */
    public function get_can_change_quantity();
    /**
     * Set whether quantity could be changed
     *
     * @param int $canChangeQuantity
     * @return $this
     */
    public function set_can_change_quantity($can_change_quantity);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Bundle\Api\Data\LinkExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Bundle\Api\Data\LinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Bundle\Api\Data\Link_Extension_Interface $extension_attributes);
}