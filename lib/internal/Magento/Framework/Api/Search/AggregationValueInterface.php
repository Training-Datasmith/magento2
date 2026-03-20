<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

/**
 * Interface Aggregation Value
 *
 * @api
 */
interface Aggregation_Value_Interface
{
    /**
     * Get aggregation
     *
     * @return string|array
     */
    public function get_value();
    /**
     * Get metrics
     *
     * @return mixed[]
     */
    public function get_metrics();
}