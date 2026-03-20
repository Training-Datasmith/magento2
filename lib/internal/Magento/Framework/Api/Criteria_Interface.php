<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api;

/**
 * Interface CriteriaInterface
 *
 * @api
 */
interface Criteria_Interface
{
    public const PART_FIELDS = 'fields';
    public const PART_FILTERS = 'filters';
    public const PART_ORDERS = 'orders';
    public const PART_CRITERIA_LIST = 'criteria_list';
    public const PART_LIMIT = 'limit';
    public const SORT_ORDER_ASC = 'ASC';
    public const SORT_ORDER_DESC = 'DESC';
    /**
     * Get associated Mapper Interface name
     *
     * @return string
     */
    public function get_mapper_interface_name();
    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function add_field($field, $alias = null);
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
     * @throws \Magento\Framework\Exception\LocalizedException if some error in the input could be detected.
     * @return void
     */
    public function add_filter($name, $field, $condition = null, $type = 'and');
    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @param bool $unShift
     * @return void
     */
    public function add_order($field, $direction = self::SORT_ORDER_DESC, $un_shift = false);
    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function set_limit($offset, $size);
    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function remove_field($field, $is_alias = false);
    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function remove_all_fields();
    /**
     * Removes filter by name
     *
     * @param string $name
     * @return void
     */
    public function remove_filter($name);
    /**
     * Removes all filters
     *
     * @return void
     */
    public function remove_all_filters();
    /**
     * Get Criteria objects added to current Composite Criteria
     *
     * @return \Magento\Framework\Api\CriteriaInterface[]
     */
    public function get_criteria_list();
    /**
     * Get list of filters
     *
     * @return string[]
     */
    public function get_filters();
    /**
     * Get ordering criteria
     *
     * @return string[]
     */
    public function get_orders();
    /**
     * Get limit
     * (['offset', 'page'])
     *
     * @return string[]
     */
    public function get_limit();
    /**
     * Retrieve criteria part
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get_part($name, $default = null);
    /**
     * Return all criteria parts as array
     *
     * @return array
     */
    public function to_array();
    /**
     * Reset criteria
     *
     * @return void
     */
    public function reset();
}