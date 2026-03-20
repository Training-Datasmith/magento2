<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model;

use Magento\Search\Model\Query_Interface;
/**
 * @api
 * @since 100.0.2
 */
interface Suggested_Queries_Interface
{
    /**#@+
     * Recommendations settings config paths
     */
    public const SEARCH_RECOMMENDATIONS_ENABLED = 'catalog/search/search_recommendations_enabled';
    public const SEARCH_RECOMMENDATIONS_COUNT_RESULTS_ENABLED = 'catalog/search/search_recommendations_count_results_enabled';
    public const SEARCH_RECOMMENDATIONS_COUNT = 'catalog/search/search_recommendations_count';
    /**#@-*/
    /**#@+
     * Suggestions settings config paths
     */
    public const SEARCH_SUGGESTION_COUNT = 'catalog/search/search_suggestion_count';
    public const SEARCH_SUGGESTION_COUNT_RESULTS_ENABLED = 'catalog/search/search_suggestion_count_results_enabled';
    public const SEARCH_SUGGESTION_ENABLED = 'catalog/search/search_suggestion_enabled';
    /**#@-*/
    /**
     * Retrieve search results
     *
     * @return \Magento\Search\Model\QueryResult[]
     */
    public function get_items(Query_Interface $query);
    /**
     * Check for counting results
     *
     * @return bool
     */
    public function is_results_count_enabled();
}