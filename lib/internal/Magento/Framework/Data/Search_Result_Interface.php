<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data;

/**
 * Interface SearchResultInterface
 *
 * @api
 */
interface Search_Result_Interface
{
    /**
     * Retrieve collection items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function get_items();
    /**
     * Retrieve count of currently loaded items
     *
     * @return int
     */
    public function get_total_count();
    /**
     * @return \Magento\Framework\Api\CriteriaInterface
     */
    public function get_search_criteria();
}