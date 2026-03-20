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
interface Product_Link_Attribute_Interface extends Extensible_Data_Interface
{
    /**
     * Get attribute code
     *
     * @return string
     */
    public function get_code();
    /**
     * Set attribute code
     *
     * @param string $code
     * @return $this
     */
    public function set_code($code);
    /**
     * Get attribute type
     *
     * @return string
     */
    public function get_type();
    /**
     * Set attribute type
     *
     * @param string $type
     * @return $this
     */
    public function set_type($type);
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Link_Attribute_Extension_Interface $extension_attributes);
}