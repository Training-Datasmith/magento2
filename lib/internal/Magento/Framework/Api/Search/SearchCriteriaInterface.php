<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\Search_Criteria_Interface as BaseSearchCriteriaInterface;
/**
 * Interface SearchCriteriaInterface
 *
 * @api
 * @package Magento\Framework\Api\Search
 * @since 100.0.2
 */
interface Search_Criteria_Interface extends Base_Search_Criteria_Interface
{
    /**
     * @return string
     */
    public function get_request_name();
    /**
     * @param string $requestName
     * @return $this
     */
    public function set_request_name($request_name);
}