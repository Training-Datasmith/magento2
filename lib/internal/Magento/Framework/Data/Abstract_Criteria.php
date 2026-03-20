<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

use Magento\Framework\Data_Object;
/**
 * Class AbstractCriteria
 */
abstract class Abstract_Criteria implements \Magento\Framework\Api\Criteria_Interface
{
    /**
     * @var array
     */
    protected $data = [self::PART_FIELDS => ['list' => []], self::PART_FILTERS => ['list' => []], self::PART_ORDERS => ['list' => []], self::PART_CRITERIA_LIST => ['list' => []]];
    /**
     * @var string
     */
    protected $mapper_interface_name;
    /**
     * Get associated Mapper Interface name
     *
     * @throws \Exception
     * @return string
     */
    public function get_mapper_interface_name()
    {
        if (!$this->mapper_interface_name) {
            throw new \Exception((string) new \Magento\Framework\Phrase('Missed Mapper Interface for Criteria Interface: %1', [get_class($this)]));
        }
        return $this->mapper_interface_name;
    }
    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function add_field($field, $alias = null)
    {
        if ($field === '*') {
            $this->data[self::PART_FIELDS]['list'] = [$field];
        } else if (is_array($field)) {
            foreach ($field as $key => $value) {
                $this->add_field($value, is_string($key) ? $key : null);
            }
        } else if ($alias === null) {
            $this->data[self::PART_FIELDS]['list'][$field] = $field;
        } else {
            $this->data[self::PART_FIELDS]['list'][$alias] = $field;
        }
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
     * </pre>
     *
     * If non matched - sequential parallel arrays are expected and OR conditions
     * will be built using above mentioned structure.
     *
     * Example:
     * <pre>
     * $field = ['age', 'name'];
     * $condition = [42, ['like' => 'Mage']];
     * $type = 'or';
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string $name
     * @param string|array $field
     * @param string|int|array $condition
     * @param string $type
     * @throws \Exception
     * @return void
     */
    public function add_filter($name, $field, $condition = null, $type = 'and')
    {
        if (isset($this->data[self::PART_FILTERS]['list'][$name])) {
            throw new \Exception((string) new \Magento\Framework\Phrase('Filter already exists in Criteria object: %1', [$name]));
        }
        $filter = new Data_Object();
        // implements ArrayAccess
        $filter['name'] = $name;
        $filter['field'] = $field;
        $filter['condition'] = $condition;
        $filter['type'] = strtolower($type);
        $this->data[self::PART_FILTERS]['list'][$name] = $filter;
    }
    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @param bool $unShift
     * @return void
     */
    public function add_order($field, $direction = self::SORT_ORDER_DESC, $un_shift = false)
    {
        $direction = strtoupper($direction) == self::SORT_ORDER_ASC ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;
        unset($this->data[self::PART_ORDERS]['list'][$field]);
        // avoid ordering by the same field twice
        if ($un_shift) {
            $orders = [$field => $direction];
            foreach ($this->data[self::PART_ORDERS]['list'] as $key => $dir) {
                $orders[$key] = $dir;
            }
            $this->data[self::PART_ORDERS]['list'] = $orders;
        } else {
            $this->data[self::PART_ORDERS]['list'][$field] = $direction;
        }
    }
    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function set_limit($offset, $size)
    {
        $this->data[self::PART_LIMIT] = [$offset, $size];
    }
    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function remove_field($field, $is_alias = false)
    {
        if ($is_alias) {
            if (isset($this->data[self::PART_FIELDS]['list'][$field])) {
                unset($this->data[self::PART_FIELDS]['list'][$field]);
            }
        } else {
            foreach ($this->data[self::PART_FIELDS]['list'] as $key => $value) {
                if ($value === $field) {
                    unset($this->data[self::PART_FIELDS]['list'][$key]);
                    break;
                }
            }
        }
    }
    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function remove_all_fields()
    {
        $this->data[self::PART_FIELDS]['list'] = [];
    }
    /**
     * Removes filter by name
     *
     * @param string $name
     * @return void
     */
    public function remove_filter($name)
    {
        if (isset($this->data[self::PART_FILTERS]['list'][$name])) {
            unset($this->data[self::PART_FILTERS]['list'][$name]);
        }
    }
    /**
     * Removes all filters
     *
     * @return void
     */
    public function remove_all_filters()
    {
        $this->data[self::PART_FILTERS]['list'] = [];
    }
    /**
     * Get Criteria objects added to current Composite Criteria
     *
     * @return array
     */
    public function get_criteria_list()
    {
        return $this->data[self::PART_CRITERIA_LIST]['list'];
    }
    /**
     * Get list of filters
     *
     * @return array
     */
    public function get_filters()
    {
        return $this->data[self::PART_FILTERS]['list'];
    }
    /**
     * Get ordering criteria
     *
     * @return array
     */
    public function get_orders()
    {
        return $this->data[self::PART_ORDERS]['list'];
    }
    /**
     * Get limit
     * (['offset', 'page'])
     *
     * @return array
     */
    public function get_limit()
    {
        return $this->data[self::PART_LIMIT];
    }
    /**
     * Retrieve criteria part
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get_part($name, $default = null)
    {
        return $this->data[$name] ?? $default;
    }
    /**
     * Return all criteria parts as array
     *
     * @return array
     */
    public function to_array()
    {
        return $this->data;
    }
    /**
     * Reset criteria
     *
     * @return void
     */
    public function reset()
    {
        $this->data = [self::PART_FIELDS => ['list' => []], self::PART_FILTERS => ['list' => []], self::PART_ORDERS => ['list' => []], self::PART_CRITERIA_LIST => ['list' => []]];
    }
}