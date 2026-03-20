<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api;

use Magento\Framework\Api\Search\Filter_Group_Builder;
/**
 * Builder for SearchCriteria Service Data Object
 *
 * @api
 */
class Search_Criteria_Builder extends Abstract_Simple_Object_Builder
{
    /**
     * @var FilterGroupBuilder
     */
    protected $_filter_group_builder;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filter_builder;
    /**
     * @param ObjectFactory $objectFactory
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(Object_Factory $object_factory, Filter_Group_Builder $filter_group_builder, Filter_Builder $filter_builder)
    {
        parent::__construct($object_factory);
        $this->_filter_group_builder = $filter_group_builder;
        $this->filter_builder = $filter_builder;
    }
    /**
     * Builds the SearchCriteria Data Object
     *
     * @return SearchCriteria
     */
    public function create()
    {
        //Initialize with empty array if not set
        if (empty($this->data[Search_Criteria::FILTER_GROUPS])) {
            $this->_set(Search_Criteria::FILTER_GROUPS, []);
        }
        return parent::create();
    }
    /**
     * Create a filter group based on the filter array provided and add to the filter groups
     *
     * @param \Magento\Framework\Api\Filter[] $filter
     * @return $this
     */
    public function add_filters(array $filter)
    {
        $this->data[Search_Criteria::FILTER_GROUPS][] = $this->_filter_group_builder->set_filters($filter)->create();
        return $this;
    }
    /**
     * Add search filter
     *
     * @param string $field
     * @param mixed $value
     * @param string $conditionType
     * @return $this
     */
    public function add_filter($field, $value, $condition_type = 'eq')
    {
        $this->add_filters([$this->filter_builder->set_field($field)->set_value($value)->set_condition_type($condition_type)->create()]);
        return $this;
    }
    /**
     * Set filter groups
     *
     * @param \Magento\Framework\Api\Search\FilterGroup[] $filterGroups
     * @return $this
     */
    public function set_filter_groups(array $filter_groups)
    {
        return $this->_set(Search_Criteria::FILTER_GROUPS, $filter_groups);
    }
    /**
     * Add sort order
     *
     * @param SortOrder $sortOrder
     * @return $this
     */
    public function add_sort_order($sort_order)
    {
        if (!isset($this->data[Search_Criteria::SORT_ORDERS])) {
            $this->data[Search_Criteria::SORT_ORDERS] = [];
        }
        $this->data[Search_Criteria::SORT_ORDERS][] = $sort_order;
        return $this;
    }
    /**
     * Set sort orders
     *
     * @param SortOrder[] $sortOrders
     * @return $this
     */
    public function set_sort_orders(array $sort_orders)
    {
        return $this->_set(Search_Criteria::SORT_ORDERS, $sort_orders);
    }
    /**
     * Set page size
     *
     * @param int $pageSize
     * @return $this
     */
    public function set_page_size($page_size)
    {
        return $this->_set(Search_Criteria::PAGE_SIZE, $page_size);
    }
    /**
     * Set current page
     *
     * @param int $currentPage
     * @return $this
     */
    public function set_current_page($current_page)
    {
        return $this->_set(Search_Criteria::CURRENT_PAGE, $current_page);
    }
}