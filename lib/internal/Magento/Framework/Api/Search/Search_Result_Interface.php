<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\Search_Results_Interface;
/**
 * Interface SearchResultInterface
 *
 * @api
 * @since 100.0.2
 */
interface Search_Result_Interface extends Search_Results_Interface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const TOTAL_COUNT = 'total_count';
    public const SEARCH_CRITERIA = 'search_criteria';
    public const ITEMS = 'items';
    public const AGGREGATIONS = 'aggregations';
    /**#@-*/
    /**
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     */
    public function get_items();
    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface[] $items
     * @return $this
     */
    public function set_items(?array $items = null);
    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function get_aggregations();
    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return $this
     */
    public function set_aggregations($aggregations);
    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface
     */
    public function get_search_criteria();
}