<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search_Criteria;

use Magento\Framework\Api\Search_Criteria_Interface;
use Magento\Framework\Data\Collection\Abstract_Db;
/**
 * @api
 * @since 101.0.0
 */
interface Collection_Processor_Interface
{
    /**
     * Apply Search Criteria to Collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @throws \InvalidArgumentException
     * @return void
     * @since 101.0.0
     */
    public function process(Search_Criteria_Interface $search_criteria, Abstract_Db $collection);
}