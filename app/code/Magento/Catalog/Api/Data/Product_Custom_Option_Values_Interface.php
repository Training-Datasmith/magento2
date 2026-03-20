<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface Product_Custom_Option_Values_Interface
{
    /**
     * Get option title
     *
     * @return string
     */
    public function get_title();
    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function set_title($title);
    /**
     * Get sort order
     *
     * @return int
     */
    public function get_sort_order();
    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function set_sort_order($sort_order);
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
     * @return string
     */
    public function get_price_type();
    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function set_price_type($price_type);
    /**
     * Get Sku
     *
     * @return string|null
     */
    public function get_sku();
    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function set_sku($sku);
    /**
     * Get Option type id
     *
     * @return int|null
     */
    public function get_option_type_id();
    /**
     * Set Option type id
     *
     * @param int $optionTypeId
     * @return int|null
     */
    public function set_option_type_id($option_type_id);
}