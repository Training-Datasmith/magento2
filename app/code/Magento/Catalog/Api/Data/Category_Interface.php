<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Category data interface.
 *
 * @api
 * @since 100.0.2
 */
interface Category_Interface extends \Magento\Framework\Api\Custom_Attributes_Data_Interface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    public const KEY_PARENT_ID = 'parent_id';
    public const KEY_NAME = 'name';
    public const KEY_IS_ACTIVE = 'is_active';
    public const KEY_POSITION = 'position';
    public const KEY_LEVEL = 'level';
    public const KEY_UPDATED_AT = 'updated_at';
    public const KEY_CREATED_AT = 'created_at';
    public const KEY_PATH = 'path';
    public const KEY_AVAILABLE_SORT_BY = 'available_sort_by';
    public const KEY_INCLUDE_IN_MENU = 'include_in_menu';
    public const KEY_PRODUCT_COUNT = 'product_count';
    public const KEY_CHILDREN_DATA = 'children_data';
    public const ATTRIBUTES = ['id', self::KEY_PARENT_ID, self::KEY_NAME, self::KEY_IS_ACTIVE, self::KEY_POSITION, self::KEY_LEVEL, self::KEY_UPDATED_AT, self::KEY_CREATED_AT, self::KEY_AVAILABLE_SORT_BY, self::KEY_INCLUDE_IN_MENU, self::KEY_CHILDREN_DATA];
    /**#@-*/
    /**
     * Retrieve category id.
     *
     * @return int|null
     */
    public function get_id();
    /**
     * Set category id.
     *
     * @param int $id
     * @return $this
     */
    public function set_id($id);
    /**
     * Get parent category ID
     *
     * @return int|null
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
     * @return string|null
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
     * @return bool|null
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
     * @return int|null
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
     * @return int|null
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
     * Retrieve children ids comma separated.
     *
     * @return string|null
     */
    public function get_children();
    /**
     * Retrieve category creation date and time.
     *
     * @return string|null
     */
    public function get_created_at();
    /**
     * Set category creation date and time.
     *
     * @param string $createdAt
     * @return $this
     */
    public function set_created_at($created_at);
    /**
     * Retrieve category last update date and time.
     *
     * @return string|null
     */
    public function get_updated_at();
    /**
     * Set category last update date and time.
     *
     * @param string $updatedAt
     * @return $this
     */
    public function set_updated_at($updated_at);
    /**
     * Retrieve category full path.
     *
     * @return string|null
     */
    public function get_path();
    /**
     * Set category full path.
     *
     * @param string $path
     * @return $this
     */
    public function set_path($path);
    /**
     * Retrieve available sort by for category.
     *
     * @return string[]|null
     */
    public function get_available_sort_by();
    /**
     * Set available sort by for category.
     *
     * @param string[]|string $availableSortBy
     * @return $this
     */
    public function set_available_sort_by($available_sort_by);
    /**
     * Get category is included in menu.
     *
     * @return bool|null
     */
    public function get_include_in_menu();
    /**
     * Set category is included in menu.
     *
     * @param bool $includeInMenu
     * @return $this
     */
    public function set_include_in_menu($include_in_menu);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\CategoryExtensionInterface|null
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function set_extension_attributes(\Magento\Catalog\Api\Data\Category_Extension_Interface $extension_attributes);
}