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
interface Product_Link_Type_Interface extends Extensible_Data_Interface
{
    /**
     * Get link type code
     *
     * @return int
     */
    public function get_code();
    /**
     * Set link type code
     *
     * @param int $code
     * @return $this
     */
    public function set_code($code);
    /**
     * Get link type name
     *
     * @return string
     */
    public function get_name();
    /**
     * Set link type name
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name);
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Link_Type_Extension_Interface $extension_attributes);
}