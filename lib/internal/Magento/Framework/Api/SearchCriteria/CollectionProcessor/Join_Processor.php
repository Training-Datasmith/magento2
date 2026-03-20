<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search_Criteria\Collection_Processor;

use Magento\Framework\Api\Search_Criteria\Collection_Processor\Join_Processor\Custom_Join_Interface;
use Magento\Framework\Api\Search_Criteria\Collection_Processor_Interface;
use Magento\Framework\Api\Search_Criteria_Interface;
use Magento\Framework\Data\Collection\Abstract_Db;
/**
 * Search criteria join processor
 */
class Join_Processor implements Collection_Processor_Interface
{
    /**
     * @var CustomJoinInterface[]
     */
    private $joins;
    /**
     * @var array
     */
    private $field_mapping;
    /**
     * @var array
     */
    private $applied_fields = [];
    /**
     * @param CustomJoinInterface[] $customJoins
     * @param array $fieldMapping
     */
    public function __construct(array $custom_joins = [], array $field_mapping = [])
    {
        $this->joins = $custom_joins;
        $this->field_mapping = $field_mapping;
    }
    /**
     * Apply Search Criteria Filters to collection only if we need this
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @return void
     */
    public function process(Search_Criteria_Interface $search_criteria, Abstract_Db $collection)
    {
        if ($search_criteria->get_filter_groups()) {
            //Process filters
            foreach ($search_criteria->get_filter_groups() as $group) {
                foreach ($group->get_filters() as $filter) {
                    if (!isset($this->applied_fields[$filter->get_field()])) {
                        $this->apply_custom_join($filter->get_field(), $collection);
                        $this->applied_fields[$filter->get_field()] = true;
                    }
                }
            }
        }
        if ($search_criteria->get_sort_orders()) {
            // Process Sortings
            foreach ($search_criteria->get_sort_orders() as $order) {
                $field = $order->get_field();
                // PHP 8.5 Compatibility: Check for null before using as array offset
                if ($field !== null && !isset($this->applied_fields[$field])) {
                    $this->apply_custom_join($field, $collection);
                    $this->applied_fields[$field] = true;
                }
            }
        }
        $this->applied_fields = [];
    }
    /**
     * Apply join to collection
     *
     * @param string $field
     * @param AbstractDb $collection
     * @return void
     */
    private function apply_custom_join($field, Abstract_Db $collection)
    {
        $field = $this->get_field_mapping($field);
        $custom_join = $this->get_custom_join($field);
        if ($custom_join) {
            $custom_join->apply($collection);
        }
    }
    /**
     * Return custom filters for field if exists
     *
     * @param string $field
     * @return CustomJoinInterface|null
     * @throws \InvalidArgumentException
     */
    private function get_custom_join($field)
    {
        $filter = null;
        if (isset($this->joins[$field])) {
            $filter = $this->joins[$field];
            if (!$this->joins[$field] instanceof Custom_Join_Interface) {
                throw new \InvalidArgumentException(sprintf('Custom join for %s must implement %s interface.', $field, Custom_Join_Interface::class));
            }
        }
        return $filter;
    }
    /**
     * Return mapped field name
     *
     * @param string $field
     * @return string
     */
    private function get_field_mapping($field)
    {
        return $this->field_mapping[$field] ?? $field;
    }
}