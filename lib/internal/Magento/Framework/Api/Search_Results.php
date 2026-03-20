<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api;

/**
 * SearchResults Service Data Object used for the search service requests
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Search_Results extends Abstract_Simple_Object implements Search_Results_Interface
{
    public const KEY_ITEMS = 'items';
    public const KEY_SEARCH_CRITERIA = 'search_criteria';
    public const KEY_TOTAL_COUNT = 'total_count';
    /**
     * Get items
     *
     * @return \Magento\Framework\Api\AbstractExtensibleObject[]
     */
    public function get_items()
    {
        return $this->_get(self::KEY_ITEMS) === null ? [] : $this->_get(self::KEY_ITEMS);
    }
    /**
     * Set items
     *
     * @param \Magento\Framework\Api\AbstractExtensibleObject[] $items
     * @return $this
     */
    public function set_items(array $items)
    {
        return $this->set_data(self::KEY_ITEMS, $items);
    }
    /**
     * Get search criteria
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    public function get_search_criteria()
    {
        return $this->_get(self::KEY_SEARCH_CRITERIA);
    }
    /**
     * Set search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function set_search_criteria(\Magento\Framework\Api\Search_Criteria_Interface $search_criteria)
    {
        return $this->set_data(self::KEY_SEARCH_CRITERIA, $search_criteria);
    }
    /**
     * Get total count
     *
     * @return int
     */
    public function get_total_count()
    {
        return $this->_get(self::KEY_TOTAL_COUNT);
    }
    /**
     * Set total count
     *
     * @param int $count
     * @return $this
     */
    public function set_total_count($count)
    {
        return $this->set_data(self::KEY_TOTAL_COUNT, $count);
    }
}