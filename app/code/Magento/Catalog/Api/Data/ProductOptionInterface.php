<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\Extensible_Data_Interface;
/**
 * Product option interface
 * @api
 * @since 100.0.2
 */
interface Product_Option_Interface extends Extensible_Data_Interface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductOptionExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Option_Extension_Interface $extension_attributes);
}