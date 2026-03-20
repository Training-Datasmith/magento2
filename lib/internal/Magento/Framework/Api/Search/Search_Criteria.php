<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\Search_Criteria as BaseSearchCriteria;
/**
 * @api
 * @since 100.0.2
 */
class Search_Criteria extends Base_Search_Criteria implements Search_Criteria_Interface
{
    public const REQUEST_NAME = 'request_name';
    /**
     * {@inheritdoc}
     */
    public function get_request_name()
    {
        return $this->_get(self::REQUEST_NAME);
    }
    /**
     * {@inheritdoc}
     */
    public function set_request_name($request_name)
    {
        return $this->set_data(self::REQUEST_NAME, $request_name);
    }
}