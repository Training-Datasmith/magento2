<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\Abstract_Simple_Object;
use Magento\Framework\Api\Search_Criteria_Interface as BaseSearchCriteriaInterface;
class Search_Result extends Abstract_Simple_Object implements Search_Result_Interface
{
    /**
     * {@inheritdoc}
     */
    public function get_aggregations()
    {
        return $this->_get(self::AGGREGATIONS);
    }
    /**
     * {@inheritdoc}
     */
    public function set_aggregations($aggregations)
    {
        return $this->set_data(self::AGGREGATIONS, $aggregations);
    }
    /**
     * {@inheritdoc}
     */
    public function get_items()
    {
        return $this->_get(self::ITEMS);
    }
    /**
     * {@inheritdoc}
     */
    public function set_items(?array $items = null)
    {
        return $this->set_data(self::ITEMS, $items);
    }
    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface
     */
    public function get_search_criteria()
    {
        return $this->_get(self::SEARCH_CRITERIA);
    }
    /**
     * Set search criteria.
     *
     * @param BaseSearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function set_search_criteria(?Base_Search_Criteria_Interface $search_criteria = null)
    {
        return $this->set_data(self::SEARCH_CRITERIA, $search_criteria);
    }
    /**
     * Get total count.
     *
     * @return int
     */
    public function get_total_count()
    {
        return $this->_get(self::TOTAL_COUNT);
    }
    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     */
    public function set_total_count($total_count)
    {
        return $this->set_data(self::TOTAL_COUNT, $total_count);
    }
}