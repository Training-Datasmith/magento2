<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Data\Collection\Entity_Factory_Interface;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Base for service collections
 */
abstract class Abstract_Service_Collection extends \Magento\Framework\Data\Collection
{
    /**
     * Filters on specific fields
     *
     * Each filter has the following structure
     * <pre>
     * [
     *     'field'     => $field,
     *     'condition' => $condition,
     * ]
     * </pre>
     * @see addFieldToFilter() for more information on conditions
     *
     * @var array
     */
    protected $field_filters = [];
    /**
     * @var FilterBuilder
     */
    protected $filter_builder;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $search_criteria_builder;
    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    protected $sort_order_builder;
    /**
     * @param EntityFactoryInterface $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(Entity_Factory_Interface $entity_factory, Filter_Builder $filter_builder, Search_Criteria_Builder $search_criteria_builder, Sort_Order_Builder $sort_order_builder)
    {
        parent::__construct($entity_factory);
        $this->filter_builder = $filter_builder;
        $this->search_criteria_builder = $search_criteria_builder;
        $this->sort_order_builder = $sort_order_builder;
    }
    /**
     * Add field filter to collection
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * <pre>
     * - ["from" => $fromValue, "to" => $toValue]
     * - ["eq" => $equalValue]
     * - ["neq" => $notEqualValue]
     * - ["like" => $likeValue]
     * - ["in" => [$inValues]]
     * - ["nin" => [$notInValues]]
     * - ["notnull" => $valueIsNotNull]
     * - ["null" => $valueIsNull]
     * - ["moreq" => $moreOrEqualValue]
     * - ["gt" => $greaterValue]
     * - ["lt" => $lessValue]
     * - ["gteq" => $greaterOrEqualValue]
     * - ["lteq" => $lessOrEqualValue]
     * - ["finset" => $valueInSet]
     * - ["regexp" => $regularExpression]
     * - ["seq" => $stringValue]
     * - ["sneq" => $stringValue]
     * </pre>
     *
     * If non matched - sequential parallel arrays are expected and OR conditions
     * will be built using above mentioned structure.
     *
     * Example:
     * <pre>
     * $field = ['age', 'name'];
     * $condition = [42, ['like' => 'Mage']];
     * or
     * ['rate', 'tax_postcode']
     * [['from'=>"3",'to'=>'8.25'], ['like' =>'%91000%']];
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string|array $field
     * @param string|int|array $condition
     * @throws LocalizedException if some error in the input could be detected.
     * @return $this
     */
    public function add_field_to_filter($field, $condition)
    {
        if (is_array($field) && is_array($condition) && count($field) != count($condition)) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The field array failed to pass. The array must have a matching condition array.'));
        } elseif (is_array($field) && !count($field) > 0) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The array of fields failed to pass. The array must include at one field.'));
        }
        $this->process_filters($field, $condition);
        return $this;
    }
    /**
     * Pre-process filters to create multiple groups in case of multiple conditions eg: from & to
     * @param string|array $field
     * @param string|int|array $condition
     * @return $this
     */
    private function process_filters($field, $condition)
    {
        //test if we have multiple conditions per field
        $requires_multiple_filter_groups = false;
        if (is_array($field) && is_array($condition)) {
            foreach ($condition as $cond) {
                if (is_array($cond) && count($cond) > 1) {
                    $requires_multiple_filter_groups = true;
                    break;
                }
            }
        } elseif (is_array($condition)) {
            $requires_multiple_filter_groups = true;
        }
        if ($requires_multiple_filter_groups) {
            $this->add_filter_groups_for_multiple_conditions($field, $condition);
        } else {
            $this->add_filter_groups_for_single_conditions($field, $condition);
        }
        return $this;
    }
    /**
     * Return a single filter group in case of single conditions
     * @param string|array $field
     * @param string|int|array $condition
     * @return $this
     */
    private function add_filter_groups_for_single_conditions($field, $condition)
    {
        $this->field_filters[] = ['field' => $field, 'condition' => $condition];
        return $this;
    }
    /**
     * Return multiple filters groups in case of multiple conditions eg: from & to
     * @param string|array $field
     * @param array $condition
     * @return $this
     */
    private function add_filter_groups_for_multiple_conditions($field, $condition)
    {
        if (!is_array($field) && is_array($condition)) {
            foreach ($condition as $key => $value) {
                $this->field_filters[] = ['field' => $field, 'condition' => [$key => $value]];
            }
        } else {
            $cnt = 0;
            foreach ($condition as $cond) {
                if (is_array($cond)) {
                    //we Do want multiple groups in this case
                    foreach ($cond as $cond_key => $cond_value) {
                        $this->field_filters[] = ['field' => array_slice($field, $cnt, 1, true), 'condition' => [$cond_key => $cond_value]];
                    }
                } else {
                    $this->field_filters[] = ['field' => array_slice($field, $cnt, 1, true), 'condition' => $cond];
                }
                $cnt++;
            }
        }
        return $this;
    }
    /**
     * Creates a search criteria DTO based on the array of field filters.
     *
     * @return SearchCriteria
     */
    protected function get_search_criteria()
    {
        foreach ($this->field_filters as $filter) {
            // array of fields, put filters in array to use 'or' group
            /** @var Filter[] $filterGroup */
            $filter_group = [];
            if (!is_array($filter['field'])) {
                // just one field
                $filter_group = [$this->create_filter_data($filter['field'], $filter['condition'])];
            } else {
                foreach ($filter['field'] as $index => $field) {
                    $filter_group[] = $this->create_filter_data($field, $filter['condition'][$index]);
                }
            }
            $this->search_criteria_builder->add_filters($filter_group);
        }
        foreach ($this->_orders as $field => $direction) {
            /** @var SortOrder $sortOrder */
            /** @var string $direction */
            $direction = $direction == 'ASC' ? Sort_Order::SORT_ASC : Sort_Order::SORT_DESC;
            $sort_order = $this->sort_order_builder->set_field($field)->set_direction($direction)->create();
            $this->search_criteria_builder->add_sort_order($sort_order);
        }
        $this->search_criteria_builder->set_current_page($this->_cur_page);
        $this->search_criteria_builder->set_page_size($this->_page_size);
        return $this->search_criteria_builder->create();
    }
    /**
     * Creates a filter DTO for given field/condition
     *
     * @param string $field Field for new filter
     * @param string|array $condition Condition for new filter.
     * @return Filter
     */
    protected function create_filter_data($field, $condition)
    {
        $this->filter_builder->set_field($field);
        if (is_array($condition)) {
            $this->filter_builder->set_value(reset($condition));
            $this->filter_builder->set_condition_type(key($condition));
        } else {
            // not an array, just use eq as condition type and given value
            $this->filter_builder->set_condition_type('eq');
            $this->filter_builder->set_value($condition);
        }
        return $this->filter_builder->create();
    }
}