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
interface Category_Product_Link_Interface extends Extensible_Data_Interface
{
    /**
     * @return string|null
     */
    public function get_sku();
    /**
     * @param string $sku
     * @return $this
     */
    public function set_sku($sku);
    /**
     * @return int|null
     */
    public function get_position();
    /**
     * @param int $position
     * @return $this
     */
    public function set_position($position);
    /**
     * Get category id
     *
     * @return string
     */
    public function get_category_id();
    /**
     * Set category id
     *
     * @param string $categoryId
     * @return $this
     */
    public function set_category_id($category_id);
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\CategoryProductLinkExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Category_Product_Link_Extension_Interface $extension_attributes);
}