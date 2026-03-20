<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\Abstract_Simple_Object_Builder;
use Magento\Framework\Api\Filter_Builder;
use Magento\Framework\Api\Object_Factory;
/**
 * Builder for FilterGroup Data.
 *
 * @api
 * @since 100.0.2
 */
class Filter_Group_Builder extends Abstract_Simple_Object_Builder
{
    /**
     * @var FilterBuilder
     */
    protected $_filter_builder;
    /**
     * @param ObjectFactory $objectFactory
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(Object_Factory $object_factory, Filter_Builder $filter_builder)
    {
        parent::__construct($object_factory);
        $this->_filter_builder = $filter_builder;
    }
    /**
     * Add filter
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return $this
     */
    public function add_filter(\Magento\Framework\Api\Filter $filter)
    {
        $this->data[Filter_Group::FILTERS][] = $filter;
        return $this;
    }
    /**
     * Set filters
     *
     * @param \Magento\Framework\Api\Filter[] $filters
     * @return $this
     */
    public function set_filters(array $filters)
    {
        return $this->_set(Filter_Group::FILTERS, $filters);
    }
}