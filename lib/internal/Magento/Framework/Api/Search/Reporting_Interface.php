<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

/**
 * Interface ReportingInterface
 *
 * @api
 */
interface Reporting_Interface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function search(Search_Criteria_Interface $search_criteria);
}