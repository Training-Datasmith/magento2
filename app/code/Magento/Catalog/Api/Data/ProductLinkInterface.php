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
interface Product_Link_Interface extends \Magento\Framework\Api\Extensible_Data_Interface
{
    /**
     * Get SKU
     *
     * @return string
     */
    public function get_sku();
    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function set_sku($sku);
    /**
     * Get link type
     *
     * @return string
     */
    public function get_link_type();
    /**
     * Set link type
     *
     * @param string $linkType
     * @return $this
     */
    public function set_link_type($link_type);
    /**
     * Get linked product sku
     *
     * @return string
     */
    public function get_linked_product_sku();
    /**
     * Set linked product sku
     *
     * @param string $linkedProductSku
     * @return $this
     */
    public function set_linked_product_sku($linked_product_sku);
    /**
     * Get linked product type (simple, virtual, etc)
     *
     * @return string
     */
    public function get_linked_product_type();
    /**
     * Set linked product type (simple, virtual, etc)
     *
     * @param string $linkedProductType
     * @return $this
     */
    public function set_linked_product_type($linked_product_type);
    /**
     * Get linked item position
     *
     * @return int
     */
    public function get_position();
    /**
     * Set linked item position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Product_Link_Extension_Interface $extension_attributes);
}