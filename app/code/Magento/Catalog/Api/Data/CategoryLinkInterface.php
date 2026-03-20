<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\Extensible_Data_Interface;
/**
 * @api
 * @since 102.0.0
 */
interface Category_Link_Interface extends Extensible_Data_Interface
{
    /**
     * @return int|null
     * @since 102.0.0
     */
    public function get_position();
    /**
     * @param int $position
     * @return $this
     * @since 102.0.0
     */
    public function set_position($position);
    /**
     * Get category id
     *
     * @return string
     * @since 102.0.0
     */
    public function get_category_id();
    /**
     * Set category id
     *
     * @param string $categoryId
     * @return $this
     * @since 102.0.0
     */
    public function set_category_id($category_id);
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\CategoryLinkExtensionInterface|null
     * @since 102.0.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CategoryLinkExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Category_Link_Extension_Interface $extension_attributes);
}