<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search_Criteria\Collection_Processor;

use Magento\Framework\Api\Search_Criteria\Collection_Processor_Interface;
use Magento\Framework\Api\Search_Criteria_Interface;
use Magento\Framework\Data\Collection\Abstract_Db;
class Pagination_Processor implements Collection_Processor_Interface
{
    /**
     * Apply Search Criteria Pagination to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @return void
     */
    public function process(Search_Criteria_Interface $search_criteria, Abstract_Db $collection)
    {
        $collection->set_cur_page($search_criteria->get_current_page());
        $collection->set_page_size($search_criteria->get_page_size());
    }
}