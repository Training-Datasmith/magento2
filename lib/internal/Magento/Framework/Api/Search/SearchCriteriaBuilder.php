<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\Abstract_Simple_Object_Builder;
use Magento\Framework\Api\Object_Factory;
use Magento\Framework\Api\Sort_Order_Builder;
/**
 * Builder for SearchCriteria Service Data Object
 *
 * @api
 * @since 100.0.2
 */
class Search_Criteria_Builder extends Abstract_Simple_Object_Builder
{
    /**
     * @var SortOrderBuilder
     */
    protected $sort_order_builder;
    /**
     * @var FilterGroupBuilder
     */
    protected $filter_group_builder;
    /**
     * @var array
     */
    private $filters = [];
    /**
     * @param ObjectFactory $objectFactory
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(Object_Factory $object_factory, Filter_Group_Builder $filter_group_builder, Sort_Order_Builder $sort_order_builder)
    {
        parent::__construct($object_factory);
        $this->sort_order_builder = $sort_order_builder;
        $this->filter_group_builder = $filter_group_builder;
    }
    /**
     * Builds the SearchCriteria Data Object
     *
     * @return SearchCriteria
     */
    public function create()
    {
        foreach ($this->filters as $filter) {
            $this->data[Search_Criteria::FILTER_GROUPS][] = $this->filter_group_builder->set_filters([])->add_filter($filter)->create();
        }
        $this->data[Search_Criteria::SORT_ORDERS] = [$this->sort_order_builder->create()];
        return parent::create();
    }
    /**
     * Create a filter group based on the filter array provided and add to the filter groups
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return $this
     */
    public function add_filter(\Magento\Framework\Api\Filter $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }
    /**
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function add_sort_order($field, $direction)
    {
        $this->sort_order_builder->set_direction($direction)->set_field($field);
        return $this;
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