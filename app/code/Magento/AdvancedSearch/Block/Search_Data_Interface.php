<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Advanced_Search\Block;

/**
 * Interface \Magento\AdvancedSearch\Block\SearchDataInterface
 *
 * @api
 */
interface Search_Data_Interface
{
    /**
     * Retrieve search suggestions
     *
     * @return array
     */
    public function get_items();
    /**
     * Check is need to show number of results
     *
     * @return bool
     */
    public function is_show_results_count();
    /**
     * Retrieve link
     *
     * @param string $queryText
     * @return string
     */
    public function get_link($query_text);
    /**
     * Retrieve title
     *
     * @return string
     */
    public function get_title();
}