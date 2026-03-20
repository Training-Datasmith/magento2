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
interface Eav_Attribute_Interface extends \Magento\Eav\Api\Data\Attribute_Interface
{
    public const IS_WYSIWYG_ENABLED = 'is_wysiwyg_enabled';
    public const IS_HTML_ALLOWED_ON_FRONT = 'is_html_allowed_on_front';
    public const USED_FOR_SORT_BY = 'used_for_sort_by';
    public const IS_FILTERABLE = 'is_filterable';
    public const IS_FILTERABLE_IN_SEARCH = 'is_filterable_in_search';
    public const IS_USED_IN_GRID = 'is_used_in_grid';
    public const IS_VISIBLE_IN_GRID = 'is_visible_in_grid';
    public const IS_FILTERABLE_IN_GRID = 'is_filterable_in_grid';
    public const POSITION = 'position';
    public const APPLY_TO = 'apply_to';
    public const IS_SEARCHABLE = 'is_searchable';
    public const IS_VISIBLE_IN_ADVANCED_SEARCH = 'is_visible_in_advanced_search';
    public const IS_COMPARABLE = 'is_comparable';
    public const IS_USED_FOR_PROMO_RULES = 'is_used_for_promo_rules';
    public const IS_VISIBLE_ON_FRONT = 'is_visible_on_front';
    public const USED_IN_PRODUCT_LISTING = 'used_in_product_listing';
    public const IS_VISIBLE = 'is_visible';
    public const SCOPE_STORE_TEXT = 'store';
    public const SCOPE_GLOBAL_TEXT = 'global';
    public const SCOPE_WEBSITE_TEXT = 'website';
    /**
     * Enable WYSIWYG flag
     *
     * @return bool|null
     */
    public function get_is_wysiwyg_enabled();
    /**
     * Set whether WYSIWYG is enabled flag
     *
     * @param bool $isWysiwygEnabled
     * @return $this
     */
    public function set_is_wysiwyg_enabled($is_wysiwyg_enabled);
    /**
     * Whether the HTML tags are allowed on the frontend
     *
     * @return bool|null
     */
    public function get_is_html_allowed_on_front();
    /**
     * Set whether the HTML tags are allowed on the frontend
     *
     * @param bool $isHtmlAllowedOnFront
     * @return $this
     */
    public function set_is_html_allowed_on_front($is_html_allowed_on_front);
    /**
     * Whether it is used for sorting in product listing
     *
     * @return bool|null
     */
    public function get_used_for_sort_by();
    /**
     * Set whether it is used for sorting in product listing
     *
     * @param bool $usedForSortBy
     * @return $this
     */
    public function set_used_for_sort_by($used_for_sort_by);
    /**
     * Whether it used in layered navigation
     *
     * @return bool|null
     */
    public function get_is_filterable();
    /**
     * Set whether it used in layered navigation
     *
     * @param bool $isFilterable
     * @return $this
     */
    public function set_is_filterable($is_filterable);
    /**
     * Whether it is used in search results layered navigation
     *
     * @return bool|null
     */
    public function get_is_filterable_in_search();
    /**
     * Whether it is used in catalog product grid
     *
     * @return bool|null
     */
    public function get_is_used_in_grid();
    /**
     * Whether it is visible in catalog product grid
     *
     * @return bool|null
     */
    public function get_is_visible_in_grid();
    /**
     * Whether it is filterable in catalog product grid
     *
     * @return bool|null
     */
    public function get_is_filterable_in_grid();
    /**
     * Set is attribute used in grid
     *
     * @param bool|null $isUsedInGrid
     * @return $this
     * @since 102.0.0
     */
    public function set_is_used_in_grid($is_used_in_grid);
    /**
     * Set is attribute visible in grid
     *
     * @param bool|null $isVisibleInGrid
     * @return $this
     * @since 102.0.0
     */
    public function set_is_visible_in_grid($is_visible_in_grid);
    /**
     * Set is attribute filterable in grid
     *
     * @param bool|null $isFilterableInGrid
     * @return $this
     * @since 102.0.0
     */
    public function set_is_filterable_in_grid($is_filterable_in_grid);
    /**
     * Set whether it is used in search results layered navigation
     *
     * @param bool $isFilterableInSearch
     * @return $this
     */
    public function set_is_filterable_in_search($is_filterable_in_search);
    /**
     * Get position
     *
     * @return int|null
     */
    public function get_position();
    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function set_position($position);
    /**
     * Get apply to value for the element
     *
     * Apply to. Empty for "Apply to all"
     * or array of the following possible values:
     *  - 'simple',
     *  - 'grouped',
     *  - 'configurable',
     *  - 'virtual',
     *  - 'bundle',
     *  - 'downloadable'
     *
     * @return string[]|null
     */
    public function get_apply_to();
    /**
     * Set apply to value for the element
     *
     * @param string[]|string $applyTo
     * @return $this
     */
    public function set_apply_to($apply_to);
    /**
     * Whether the attribute can be used in Quick Search
     *
     * @return string|null
     */
    public function get_is_searchable();
    /**
     * Whether the attribute can be used in Quick Search
     *
     * @param string $isSearchable
     * @return $this
     */
    public function set_is_searchable($is_searchable);
    /**
     * Whether the attribute can be used in Advanced Search
     *
     * @return string|null
     */
    public function get_is_visible_in_advanced_search();
    /**
     * Set whether the attribute can be used in Advanced Search
     *
     * @param string $isVisibleInAdvancedSearch
     * @return $this
     */
    public function set_is_visible_in_advanced_search($is_visible_in_advanced_search);
    /**
     * Whether the attribute can be compared on the frontend
     *
     * @return string|null
     */
    public function get_is_comparable();
    /**
     * Set whether the attribute can be compared on the frontend
     *
     * @param string $isComparable
     * @return $this
     */
    public function set_is_comparable($is_comparable);
    /**
     * Whether the attribute can be used for promo rules
     *
     * @return string|null
     */
    public function get_is_used_for_promo_rules();
    /**
     * Set whether the attribute can be used for promo rules
     *
     * @param string $isUsedForPromoRules
     * @return $this
     */
    public function set_is_used_for_promo_rules($is_used_for_promo_rules);
    /**
     * Whether the attribute is visible on the frontend
     *
     * @return string|null
     */
    public function get_is_visible_on_front();
    /**
     * Set whether the attribute is visible on the frontend
     *
     * @param string $isVisibleOnFront
     * @return $this
     */
    public function set_is_visible_on_front($is_visible_on_front);
    /**
     * Whether the attribute can be used in product listing
     *
     * @return string|null
     */
    public function get_used_in_product_listing();
    /**
     * Set whether the attribute can be used in product listing
     *
     * @param string $usedInProductListing
     * @return $this
     */
    public function set_used_in_product_listing($used_in_product_listing);
    /**
     * Whether attribute is visible on frontend.
     *
     * @return bool|null
     */
    public function get_is_visible();
    /**
     * Set whether attribute is visible on frontend.
     *
     * @param bool $isVisible
     * @return $this
     */
    public function set_is_visible($is_visible);
    /**
     * Retrieve attribute scope
     *
     * @return string|null
     */
    public function get_scope();
    /**
     * Set attribute scope
     *
     * @param string $scope
     * @return $this
     */
    public function set_scope($scope);
    /**
     * @return \Magento\Catalog\Api\Data\EavAttributeExtensionInterface|null
     */
    public function get_extension_attributes();
}