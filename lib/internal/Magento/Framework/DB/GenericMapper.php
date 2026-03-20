<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\Api\Criteria_Interface;
/**
 * Class GenericMapper
 */
class Generic_Mapper extends Abstract_Mapper
{
    /**
     * Set initial conditions
     *
     * @return void
     */
    protected function init()
    {
    }
    /**
     * Map criteria list
     *
     * @param \Magento\Framework\Api\CriteriaInterface[] $criteriaList
     * @return void
     */
    public function map_criteria_list(array $criteria_list)
    {
        foreach ($criteria_list as $criteria) {
            /** @var CriteriaInterface $criteria */
            $mapper = $criteria->get_mapper_interface_name();
            $mapper_instance = $this->mapper_factory->create($mapper, ['select' => $this->select]);
            $this->select = $mapper_instance->map($criteria);
        }
    }
    /**
     * Map filters
     *
     * @param array $filters
     * @return void
     */
    public function map_filters(array $filters)
    {
        $this->render_filters_before();
        foreach ($filters as $filter) {
            switch ($filter['type']) {
                case 'or':
                    $condition = $this->get_connection()->quote_into($filter['field'] . '=?', $filter['condition']);
                    $this->get_select()->or_where($condition);
                    break;
                case 'string':
                    $this->get_select()->where($filter['condition']);
                    break;
                case 'public':
                    $field = $this->get_mapped_field($filter['field']);
                    $condition = $filter['condition'];
                    $this->get_select()->where($this->get_condition_sql($field, $condition), null, Select::TYPE_CONDITION);
                    break;
                default:
                    $condition = $this->get_connection()->quote_into($filter['field'] . '=?', $filter['condition']);
                    $this->get_select()->where($condition);
            }
        }
    }
    /**
     * Map order
     *
     * @param array $orders
     * @return void
     */
    public function map_orders(array $orders)
    {
        foreach ($orders as $field => $direction) {
            $this->select->order(new \Zend_Db_Expr($field . ' ' . $direction));
        }
    }
    /**
     * Map fields
     *
     * @param array $fields
     * @throws \Zend_Db_Select_Exception
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function map_fields(array $fields)
    {
        $columns = $this->get_select()->get_part(\Magento\Framework\DB\Select::COLUMNS);
        $selected_unique_names = [];
        foreach ($fields as $field_info) {
            if (is_string($field_info)) {
                $field_info = isset($this->map[$field_info]) ? $this->map[$field_info] : $field_info;
            }
            list($correlation_name, $field, $alias) = $field_info;
            if (!is_string($alias)) {
                $alias = null;
            }
            if ($field instanceof \Zend_Db_Expr) {
                $field = $field->__toString();
            }
            $selected_unique_name = $alias ?: $field;
            if (in_array($selected_unique_name, $selected_unique_names)) {
                // ignore field since the alias is already used by another field
                continue;
            }
            $selected_unique_names[] = $selected_unique_name;
            $columns[] = [$correlation_name, $field, $alias];
        }
        $this->get_select()->set_part(\Magento\Framework\DB\Select::COLUMNS, $columns);
    }
    /**
     * Map limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function map_limit($offset, $size)
    {
        $this->select->limit_page($offset, $size);
    }
    /**
     * Map distinct flag
     *
     * @param bool $flag
     * @return void
     */
    public function map_distinct($flag)
    {
        $this->select->distinct($flag);
    }
}