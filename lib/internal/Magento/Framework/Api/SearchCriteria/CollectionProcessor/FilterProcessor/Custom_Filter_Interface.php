<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search_Criteria\Collection_Processor\Filter_Processor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\Abstract_Db;
/**
 * @api
 * @since 101.0.0
 */
interface Custom_Filter_Interface
{
    /**
     * Apply Custom Filter to Collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter was applied
     * @since 101.0.0
     */
    public function apply(Filter $filter, Abstract_Db $collection);
}