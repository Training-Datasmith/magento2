<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search_Criteria\Collection_Processor;

use Magento\Framework\Api\Search_Criteria\Collection_Processor_Interface;
use Magento\Framework\Api\Search_Criteria_Interface;
use Magento\Framework\Api\Sort_Order;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\Abstract_Db;
class Sorting_Processor implements Collection_Processor_Interface
{
    /**
     * @var array
     */
    private $field_mapping;
    /**
     * @var array
     */
    private $default_orders;
    /**
     * @param array $fieldMapping
     * @param array $defaultOrders
     */
    public function __construct(array $field_mapping = [], array $default_orders = [])
    {
        $this->field_mapping = $field_mapping;
        $this->default_orders = $default_orders;
    }
    /**
     * Apply Search Criteria Sorting Orders to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @return void
     */
    public function process(Search_Criteria_Interface $search_criteria, Abstract_Db $collection)
    {
        if ($search_criteria->get_sort_orders()) {
            $this->apply_orders($search_criteria->get_sort_orders(), $collection);
        } elseif ($this->default_orders) {
            $this->apply_default_orders($collection);
        }
    }
    /**
     * Return mapped field name
     *
     * @param string $field
     * @return string
     */
    private function get_field_mapping($field)
    {
        return $this->field_mapping[$field ?? ''] ?? $field;
    }
    /**
     * Apply sort orders to collection
     *
     * @param SortOrder[] $sortOrders
     * @param AbstractDb $collection
     * @return void
     */
    private function apply_orders(array $sort_orders, Abstract_Db $collection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sort_orders as $sort_order) {
            $field = $this->get_field_mapping($sort_order->get_field());
            if (null !== $field) {
                $order = $sort_order->get_direction() == Sort_Order::SORT_ASC ? Collection::SORT_ORDER_ASC : Collection::SORT_ORDER_DESC;
                $collection->add_order($field, $order);
            }
        }
    }
    /**
     * Apply default orders to collection
     *
     * @param AbstractDb $collection
     * @return void
     */
    private function apply_default_orders(Abstract_Db $collection)
    {
        foreach ($this->default_orders as $field => $direction) {
            $field = $this->get_field_mapping($field);
            if (null !== $field) {
                $order = $direction == Sort_Order::SORT_ASC ? Collection::SORT_ORDER_ASC : Collection::SORT_ORDER_DESC;
                $collection->add_order($field, $order);
            }
        }
    }
}