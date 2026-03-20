<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api\Search_Criteria\Collection_Processor;

use Magento\Framework\Api\Combined_Filter_Group;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search_Criteria\Collection_Processor\Condition_Processor\Custom_Condition_Interface;
use Magento\Framework\Api\Search_Criteria\Collection_Processor\Condition_Processor\Custom_Condition_Provider_Interface;
use Magento\Framework\Api\Search_Criteria\Collection_Processor_Interface;
use Magento\Framework\Api\Search_Criteria_Interface;
use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Phrase;
use Magento\Framework\Search\Adapter\Mysql\Condition_Manager;
/**
 * Collection processor that adds filters to collection based on passed search criteria
 *
 * Difference between FilterProcessor is that AdvancedFilterProcessor gives ability
 * to add filters using different combination strategies
 *
 * For example you can add such filters:
 *
 * Select * FROM some_table
 * WHERE
 *  field_1 = 10
 *  AND (
 *      field_2 in (1,2,3)
 *      OR
 *      field_3 like '%banana%'
 *  )
 */
class Advanced_Filter_Processor implements Collection_Processor_Interface
{
    /**
     * @var CustomConditionProviderInterface
     */
    private $custom_condition_provider;
    /**
     * @var CustomConditionInterface
     */
    private $default_condition_processor;
    /**
     * @var ConditionManager
     */
    private $condition_manager;
    /**
     * @param CustomConditionInterface $defaultConditionProcessor
     * @param ConditionManager $conditionManager
     * @param CustomConditionProviderInterface $customConditionProvider
     */
    public function __construct(Custom_Condition_Interface $default_condition_processor, Condition_Manager $condition_manager, Custom_Condition_Provider_Interface $custom_condition_provider)
    {
        $this->default_condition_processor = $default_condition_processor;
        $this->condition_manager = $condition_manager;
        $this->custom_condition_provider = $custom_condition_provider;
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
            $conditions = $this->get_conditions_from_filter_group($group);
            $collection->get_select()->where($conditions);
        }
    }
    /**
     * Add FilterGroup to the collection
     *
     * @param CombinedFilterGroup $filterGroup
     * @return string
     * @throws InputException
     */
    private function get_conditions_from_filter_group(Combined_Filter_Group $filter_group): string
    {
        $conditions = [];
        foreach ($filter_group->get_filters() as $filter) {
            if ($filter instanceof Combined_Filter_Group) {
                $conditions[] = $this->get_conditions_from_filter_group($filter);
                continue;
            }
            if ($filter instanceof Filter) {
                $conditions[] = $this->get_conditions_from_filter($filter);
                continue;
            }
            throw new Input_Exception(new Phrase('Undefined filter group "%1" passed in.', [get_class($filter)]));
        }
        return $this->condition_manager->wrap_brackets($this->condition_manager->combine_queries($conditions, $filter_group->get_combination_mode()));
    }
    /**
     * @param Filter $filter
     * @return string
     * @throws InputException
     */
    private function get_conditions_from_filter(Filter $filter): string
    {
        if ($this->custom_condition_provider->has_processor_for_field($filter->get_field())) {
            $custom_processor = $this->custom_condition_provider->get_processor_by_field($filter->get_field());
            return $custom_processor->build($filter);
        }
        return $this->default_condition_processor->build($filter);
    }
}