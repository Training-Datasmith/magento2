<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\Extensible_Data_Interface;
/**
 * Product type details
 * @api
 * @since 100.0.2
 */
interface Product_Type_Interface extends Extensible_Data_Interface
{
    /**
     * Get product type code
     *
     * @return string
     */
    public function get_name();
    /**
     * Set product type code
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name);
    /**
     * Get product type label
     *
     * @return string
     */
    public function get_label();
    /**
     * Set product type label
     *
     * @param string $label
     * @return $this
     */
    public function set_label($label);
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductTypeExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Type_Extension_Interface $extension_attributes);
}