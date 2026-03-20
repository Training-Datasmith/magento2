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
interface Category_Tree_Interface
{
    /**
     * Get Id
     *
     * @return int|null
     */
    public function get_id();
    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function set_id($id);
    /**
     * Get parent category ID
     *
     * @return int
     */
    public function get_parent_id();
    /**
     * Set parent category ID
     *
     * @param int $parentId
     * @return $this
     */
    public function set_parent_id($parent_id);
    /**
     * Get category name
     *
     * @return string
     */
    public function get_name();
    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name);
    /**
     * Check whether category is active
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_is_active();
    /**
     * Set whether category is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function set_is_active($is_active);
    /**
     * Get category position
     *
     * @return int
     */
    public function get_position();
    /**
     * Set category position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position);
    /**
     * Get category level
     *
     * @return int
     */
    public function get_level();
    /**
     * Set category level
     *
     * @param int $level
     * @return $this
     */
    public function set_level($level);
    /**
     * Get product count
     *
     * @return int
     */
    public function get_product_count();
    /**
     * Set product count
     *
     * @param int $productCount
     * @return $this
     */
    public function set_product_count($product_count);
    /**
     * Get Children Data
     *
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface[]
     */
    public function get_children_data();
    /**
     * Set Children Data
     *
     * @param \Magento\Catalog\Api\Data\CategoryTreeInterface[] $childrenData
     * @return $this
     */
    public function set_children_data(?array $children_data = null);
}