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
interface Product_Custom_Option_Type_Interface extends Extensible_Data_Interface
{
    /**
     * Get option type label
     *
     * @return string
     */
    public function get_label();
    /**
     * Set option type label
     *
     * @param string $label
     * @return $this
     */
    public function set_label($label);
    /**
     * Get option type code
     *
     * @return string
     */
    public function get_code();
    /**
     * Set option type code
     *
     * @param string $code
     * @return $this
     */
    public function set_code($code);
    /**
     * Get option type group
     *
     * @return string
     */
    public function get_group();
    /**
     * Set option type group
     *
     * @param string $group
     * @return $this
     */
    public function set_group($group);
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Custom_Option_Type_Extension_Interface $extension_attributes);
}