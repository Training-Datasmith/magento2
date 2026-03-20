<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Data Object for SearchCriteria
 * @codeCoverageIgnore
 */
class Search_Criteria extends Abstract_Simple_Object implements Search_Criteria_Interface
{
    /**#@+
     * Constants for Data Object keys
     */
    public const FILTER_GROUPS = 'filter_groups';
    public const SORT_ORDERS = 'sort_orders';
    public const PAGE_SIZE = 'page_size';
    public const CURRENT_PAGE = 'current_page';
    /**
     * Get a list of filter groups.
     *
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     */
    public function get_filter_groups()
    {
        $filter_groups = $this->_get(self::FILTER_GROUPS);
        return is_array($filter_groups) ? $filter_groups : [];
    }
    /**
     * Get sort order.
     *
     * @return \Magento\Framework\Api\SortOrder[]|null
     */
    public function get_sort_orders()
    {
        return $this->_get(self::SORT_ORDERS);
    }
    /**
     * Get page size.
     *
     * @return int|null
     */
    public function get_page_size()
    {
        return $this->_get(self::PAGE_SIZE);
    }
    /**
     * Get current page.
     *
     * @return int|null
     */
    public function get_current_page()
    {
        return $this->_get(self::CURRENT_PAGE);
    }
    /**
     * Set a list of filter groups.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup[] $filterGroups
     * @return $this
     */
    public function set_filter_groups(?array $filter_groups = null)
    {
        return $this->set_data(self::FILTER_GROUPS, $filter_groups);
    }
    /**
     * Set sort order.
     *
     * @param \Magento\Framework\Api\SortOrder[] $sortOrders
     * @return $this
     */
    public function set_sort_orders(?array $sort_orders = null)
    {
        return $this->set_data(self::SORT_ORDERS, $sort_orders);
    }
    /**
     * Set page size.
     *
     * @param int $pageSize
     * @return $this
     */
    public function set_page_size($page_size)
    {
        return $this->set_data(self::PAGE_SIZE, $page_size);
    }
    /**
     * Set current page.
     *
     * @param int $currentPage
     * @return $this
     */
    public function set_current_page($current_page)
    {
        return $this->set_data(self::CURRENT_PAGE, $current_page);
    }
}