<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Widget\Grid\Column;
/**
 * Grid column filter interface
 *
 * @api
 * @since 100.0.2
 */
interface Filter_Interface
{
    /**
     * Retrieve column related to filter
     *
     * @return Column
     */
    public function get_column();
    /**
     * Set column related to filter
     *
     * @param Column $column
     * @return AbstractFilter
     */
    public function set_column($column);
    /**
     * Retrieve filter html
     *
     * @return string
     */
    public function get_html();
}