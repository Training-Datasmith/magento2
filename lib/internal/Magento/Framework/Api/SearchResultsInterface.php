<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api;

/**
 * Search results interface.
 *
 * @api
 * @since 100.0.2
 */
interface Search_Results_Interface
{
    /**
     * Get items list.
     *
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function get_items();
    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     */
    public function set_items(array $items);
    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface
     */
    public function get_search_criteria();
    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function set_search_criteria(\Magento\Framework\Api\Search_Criteria_Interface $search_criteria);
    /**
     * Get total count.
     *
     * @return int
     */
    public function get_total_count();
    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     */
    public function set_total_count($total_count);
}