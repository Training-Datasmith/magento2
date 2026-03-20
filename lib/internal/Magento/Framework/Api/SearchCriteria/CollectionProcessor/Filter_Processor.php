<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search_Criteria\Collection_Processor;

use Magento\Framework\Api\Search\Filter_Group;
use Magento\Framework\Api\Search_Criteria\Collection_Processor\Filter_Processor\Custom_Filter_Interface;
use Magento\Framework\Api\Search_Criteria\Collection_Processor_Interface;
use Magento\Framework\Api\Search_Criteria_Interface;
use Magento\Framework\Data\Collection\Abstract_Db;
class Filter_Processor implements Collection_Processor_Interface
{
    /**
     * @var CustomFilterInterface[]
     */
    private $custom_filters;
    /**
     * @var array
     */
    private $field_mapping;
    /**
     * @param CustomFilterInterface[] $customFilters
     * @param array $fieldMapping
     */
    public function __construct(array $custom_filters = [], array $field_mapping = [])
    {
        $this->custom_filters = $custom_filters;
        $this->field_mapping = $field_mapping;
    }
    /**
     * Apply Search Criteria Filters to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @return void
     */
    public function process(Search_Criteria_Interface $search_criteria, Abstract_Db $collection)
    {
        foreach ($search_criteria->get_filter_groups() as $group) {
            $this->add_filter_group_to_collection($group, $collection);
        }
    }
    /**
     * Add FilterGroup to the collection
     *
     * @param FilterGroup $filterGroup
     * @param AbstractDb $collection
     * @return void
     */
    private function add_filter_group_to_collection(Filter_Group $filter_group, Abstract_Db $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filter_group->get_filters() as $filter) {
            $is_applied = false;
            $custom_filter = $this->get_custom_filter_for_field($filter->get_field());
            if ($custom_filter) {
                $is_applied = $custom_filter->apply($filter, $collection);
            }
            if (!$is_applied) {
                $condition = $filter->get_condition_type() ? $filter->get_condition_type() : 'eq';
                $fields[] = $this->get_field_mapping($filter->get_field());
                if ($condition === 'fulltext') {
                    // NOTE: This is not a fulltext search, but the best way to search something when
                    // a SearchCriteria with "fulltext" condition is provided over a MySQL table
                    // (see https://github.com/magento-engcom/msi/issues/1221)
                    $condition = 'like';
                    $filter->set_value('%' . $filter->get_value() . '%');
                }
                $conditions[] = [$condition => $filter->get_value()];
            }
        }
        $this->check_from_to($fields, $conditions);
        if ($fields) {
            $collection->add_field_to_filter($fields, $conditions);
        }
    }
    /**
     * Return custom filters for field if exists
     *
     * @param string $field
     * @return CustomFilterInterface|null
     * @throws \InvalidArgumentException
     */
    private function get_custom_filter_for_field($field)
    {
        $filter = null;
        if (isset($this->custom_filters[$field])) {
            $filter = $this->custom_filters[$field];
            if (!$this->custom_filters[$field] instanceof Custom_Filter_Interface) {
                throw new \InvalidArgumentException(sprintf('Filter for %s must implement %s interface.', $field, Custom_Filter_Interface::class));
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
    /**
     * Check filtergoup for type from & to
     *
     * @param string[] $fields
     * @param array<string[]> $conditions
     * @return void
     */
    private function check_from_to(&$fields, &$conditions)
    {
        $_fields = array_unique($fields);
        $_conditions = [];
        foreach ($conditions as $condition) {
            $_conditions[array_key_first($condition)] = reset($condition);
        }
        if (count($_fields) == 1 && count($_conditions) == 2 && isset($_conditions['from']) && isset($_conditions['to'])) {
            $fields = $_fields;
            $conditions = [$_conditions];
        }
    }
}