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
interface Product_Attribute_Type_Interface extends Extensible_Data_Interface
{
    public const VALUE = 'value';
    public const LABEL = 'label';
    /**
     * Get value
     *
     * @return string
     */
    public function get_value();
    /**
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function set_value($value);
    /**
     * Get type label
     *
     * @return string
     */
    public function get_label();
    /**
     * Set type label
     *
     * @param string $label
     * @return $this
     */
    public function set_label($label);
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeTypeExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Attribute_Type_Extension_Interface $extension_attributes);
}