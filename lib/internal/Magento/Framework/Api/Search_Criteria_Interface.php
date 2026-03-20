<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Search criteria interface.
 *
 * @api
 * @since 100.0.2
 */
interface Search_Criteria_Interface
{
    /**
     * Get a list of filter groups.
     *
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     */
    public function get_filter_groups();
    /**
     * Set a list of filter groups.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup[] $filterGroups
     * @return $this
     */
    public function set_filter_groups(?array $filter_groups = null);
    /**
     * Get sort order.
     *
     * @return \Magento\Framework\Api\SortOrder[]|null
     */
    public function get_sort_orders();
    /**
     * Set sort order.
     *
     * @param \Magento\Framework\Api\SortOrder[] $sortOrders
     * @return $this
     */
    public function set_sort_orders(?array $sort_orders = null);
    /**
     * Get page size.
     *
     * @return int|null
     */
    public function get_page_size();
    /**
     * Set page size.
     *
     * @param int $pageSize
     * @return $this
     */
    public function set_page_size($page_size);
    /**
     * Get current page.
     *
     * @return int|null
     */
    public function get_current_page();
    /**
     * Set current page.
     *
     * @param int $currentPage
     * @return $this
     */
    public function set_current_page($current_page);
}