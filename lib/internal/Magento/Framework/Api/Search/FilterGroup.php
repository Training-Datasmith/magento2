<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\Abstract_Simple_Object;
/**
 * Groups two or more filters together using a logical OR
 *
 * @api
 * @since 100.0.2
 */
class Filter_Group extends Abstract_Simple_Object
{
    public const FILTERS = 'filters';
    /**
     * Returns a list of filters in this group
     *
     * @return \Magento\Framework\Api\Filter[]|null
     */
    public function get_filters()
    {
        $filters = $this->_get(self::FILTERS);
        return $filters === null ? [] : $filters;
    }
    /**
     * Set filters
     *
     * @param \Magento\Framework\Api\Filter[] $filters
     * @return $this
     * @codeCoverageIgnore
     */
    public function set_filters(?array $filters = null)
    {
        return $this->set_data(self::FILTERS, $filters);
    }
}