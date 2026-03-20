<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

/**
 * Search API for all requests
 *
 * @api
 * @since 100.0.2
 */
interface Search_Interface
{
    /**
     * Make Full Text Search and return found Documents
     *
     * @param \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     */
    public function search(\Magento\Framework\Api\Search\Search_Criteria_Interface $search_criteria);
}