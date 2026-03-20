<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Data\Collection\Entity_Factory_Interface;
use Magento\Framework\Data_Object;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Option\Array_Interface;
/**
 * Data collection
 *
 * TODO: Refactor use of \Magento\Framework\Option\ArrayInterface in library.
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Collection implements \IteratorAggregate, \Countable, Array_Interface, Collection_Data_Source_Interface, Reset_After_Request_Interface
{
    public const SORT_ORDER_ASC = 'ASC';
    public const SORT_ORDER_DESC = 'DESC';
    /**
     * Collection items
     *
     * @var DataObject[]
     */
    protected $_items = [];
    /**
     * Item object class name
     *
     * @var string
     */
    protected $_item_object_class = Data_Object::class;
    /**
     * Order configuration
     *
     * @var array
     */
    protected $_orders = [];
    /**
     * Filters configuration
     *
     * @var DataObject[]
     */
    protected $_filters = [];
    /**
     * Filter rendered flag
     *
     * @var bool
     */
    protected $_is_filters_rendered = false;
    /**
     * Current page number for items pager
     *
     * @var int
     */
    protected $_cur_page = 1;
    /**
     * Pager page size
     *
     * if page size is false, then we work with all items
     *
     * @var int|false
     */
    protected $_page_size = false;
    /**
     * Total items number
     *
     * @var int
     */
    protected $_total_records;
    /**
     * Loading state flag
     *
     * @var bool
     */
    protected $_is_collection_loaded;
    /**
     * Additional collection flags
     *
     * @var array
     */
    protected $_flags = [];
    /**
     * @var EntityFactoryInterface
     */
    protected $_entity_factory;
    /**
     * @param EntityFactoryInterface $entityFactory
     */
    public function __construct(Entity_Factory_Interface $entity_factory)
    {
        $this->_entity_factory = $entity_factory;
    }
    /**
     * Add collection filter
     *
     * @param string $field
     * @param string $value
     * @param string $type and|or|string
     * @return $this
     */
    public function add_filter($field, $value, $type = 'and')
    {
        $filter = new Data_Object();
        // implements ArrayAccess
        $filter['field'] = $field;
        $filter['value'] = $value;
        $filter['type'] = strtolower($type);
        $this->_filters[] = $filter;
        $this->_is_filters_rendered = false;
        return $this;
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
     * If non-matched - sequential parallel arrays are expected and OR conditions
     * will be built using above-mentioned structure.
     *
     * Example:
     * <pre>
     * $field = ['age', 'name'];
     * $condition = [42, ['like' => 'Mage']];
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string|array $field
     * @param string|int|array $condition
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException if some error in the input could be detected.
     */
    public function add_field_to_filter($field, $condition)
    {
        throw new \Magento\Framework\Exception\Localized_Exception(new \Magento\Framework\Phrase('Not implemented'));
    }
    /**
     * Search for a filter by specified field
     *
     * Multiple filters can be matched if an array is specified:
     * - 'foo' -- get the first filter with field name 'foo'
     * - array('foo') -- get all filters with field name 'foo'
     * - array('foo', 'bar') -- get all filters with field name 'foo' or 'bar'
     * - array() -- get all filters
     *
     * @param string|string[] $field
     * @return DataObject|DataObject[]|void
     */
    public function get_filter($field)
    {
        if (is_array($field)) {
            // empty array: get all filters
            if (empty($field)) {
                return $this->_filters;
            }
            // non-empty array: collect all filters that match specified field names
            $result = [];
            foreach ($this->_filters as $filter) {
                if (in_array($filter['field'], $field)) {
                    $result[] = $filter;
                }
            }
            return $result;
        }
        // get a first filter by specified name
        foreach ($this->_filters as $filter) {
            if ($filter['field'] === $field) {
                return $filter;
            }
        }
    }
    /**
     * Retrieve collection loading status
     *
     * @return bool
     */
    public function is_loaded()
    {
        return $this->_is_collection_loaded;
    }
    /**
     * Set collection loading status flag
     *
     * @param bool $flag
     * @return $this
     */
    protected function _set_is_loaded($flag = true)
    {
        $this->_is_collection_loaded = $flag;
        return $this;
    }
    /**
     * Get current collection page
     *
     * @param int $displacement
     * @return int
     */
    public function get_cur_page($displacement = 0)
    {
        if ($this->_cur_page + $displacement < 1) {
            return 1;
        }
        return $this->_cur_page + $displacement;
    }
    /**
     * Retrieve collection last page number
     *
     * @return int
     */
    public function get_last_page_number()
    {
        $collection_size = (int) $this->get_size();
        if (0 === $collection_size) {
            return 1;
        } elseif ($this->_page_size) {
            return (int) ceil($collection_size / $this->_page_size);
        }
        return 1;
    }
    /**
     * Retrieve collection page size
     *
     * @return int
     */
    public function get_page_size()
    {
        return $this->_page_size;
    }
    /**
     * Retrieve collection all items count
     *
     * @return int
     */
    public function get_size()
    {
        $this->load();
        if ($this->_total_records === null) {
            $this->_total_records = count($this->get_items());
        }
        return (int) $this->_total_records;
    }
    /**
     * Retrieve collection first item
     *
     * @return DataObject
     */
    public function get_first_item()
    {
        $this->load();
        if (count($this->_items)) {
            reset($this->_items);
            return current($this->_items);
        }
        return $this->_entity_factory->create($this->_item_object_class);
    }
    /**
     * Retrieve collection last item
     *
     * @return DataObject
     */
    public function get_last_item()
    {
        $this->load();
        if (count($this->_items)) {
            return end($this->_items);
        }
        return $this->_entity_factory->create($this->_item_object_class);
    }
    /**
     * Retrieve collection items
     *
     * @return DataObject[]
     */
    public function get_items()
    {
        $this->load();
        return $this->_items;
    }
    /**
     * Retrieve field values from all items
     *
     * @param string $colName
     * @return array
     */
    public function get_column_values($col_name)
    {
        $this->load();
        $col = [];
        foreach ($this->get_items() as $item) {
            $col[] = $item->get_data($col_name);
        }
        return $col;
    }
    /**
     * Search all items by field value
     *
     * @param string $column
     * @param float|int|null|string $value
     * @return array
     */
    public function get_items_by_column_value($column, $value)
    {
        $this->load();
        $res = [];
        foreach ($this as $item) {
            if ($item->get_data($column) == $value) {
                $res[] = $item;
            }
        }
        return $res;
    }
    /**
     * Search first item by field value
     *
     * @param string $column
     * @param string|int $value
     * @return DataObject|null
     */
    public function get_item_by_column_value($column, $value)
    {
        $this->load();
        foreach ($this as $item) {
            if ($item->get_data($column) == $value) {
                return $item;
            }
        }
        return null;
    }
    /**
     * Adding item to item array
     *
     * @param DataObject $item
     * @return $this
     * @throws \Exception
     */
    public function add_item(Data_Object $item)
    {
        $item_id = $this->_get_item_id($item);
        if ($item_id !== null) {
            if (isset($this->_items[$item_id])) {
                //phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception('Item (' . get_class($item) . ') with the same ID "' . $item->get_id() . '" already exists.');
            }
            $this->_items[$item_id] = $item;
        } else {
            $this->_add_item($item);
        }
        return $this;
    }
    /**
     * Add item that has no id to collection
     *
     * @param DataObject $item
     * @return $this
     */
    protected function _add_item($item)
    {
        $this->_items[] = $item;
        return $this;
    }
    /**
     * Retrieve item id
     *
     * @param DataObject $item
     * @return string|int
     */
    protected function _get_item_id(Data_Object $item)
    {
        return $item->get_id();
    }
    /**
     * Retrieve ids of all items
     *
     * @return array
     */
    public function get_all_ids()
    {
        $ids = [];
        foreach ($this->get_items() as $item) {
            $ids[] = $this->_get_item_id($item);
        }
        return $ids;
    }
    /**
     * Remove item from collection by item key
     *
     * @param string $key
     * @return $this
     */
    public function remove_item_by_key($key)
    {
        if (isset($this->_items[$key])) {
            unset($this->_items[$key]);
        }
        return $this;
    }
    /**
     * Remove all items from collection
     *
     * @return $this
     */
    public function remove_all_items()
    {
        $this->_items = [];
        return $this;
    }
    /**
     * Clear collection
     *
     * @return $this
     */
    public function clear()
    {
        $this->_set_is_loaded(false);
        $this->_items = [];
        $this->_total_records = null;
        return $this;
    }
    /**
     * Walk through the collection and run model method or external callback with optional arguments
     *
     * Returns array with results of callback for each item
     *
     * @param callable $callback
     * @param array $args
     * @return array
     */
    public function walk($callback, array $args = [])
    {
        $results = [];
        $use_item_callback = is_string($callback) && strpos($callback, '::') === false;
        foreach ($this->get_items() as $id => $item) {
            $params = $args;
            if ($use_item_callback) {
                $cb = [$item, $callback];
            } else {
                $cb = $callback;
                array_unshift($params, $item);
            }
            // The `array_values` is a workaround to ensure the same behavior in PHP 7 and 8.
            $results[$id] = call_user_func_array($cb, array_values($params));
        }
        return $results;
    }
    /**
     * Call method or callback on each item in the collection.
     *
     * @param string|array|\Closure $objMethod
     * @param array $args
     * @return void
     */
    public function each($obj_method, $args = [])
    {
        if ($obj_method instanceof \Closure) {
            foreach ($this->get_items() as $item) {
                $obj_method($item, ...$args);
            }
        } elseif (is_array($obj_method)) {
            foreach ($this->get_items() as $item) {
                call_user_func($obj_method, $item, ...$args);
            }
        } else {
            foreach ($this->get_items() as $item) {
                $item->{$obj_method}(...$args);
            }
        }
    }
    /**
     * Setting data for all collection items
     *
     * @param string $key
     * @param string|int|null $value
     * @return $this
     */
    public function set_data_to_all($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set_data_to_all($k, $v);
            }
            return $this;
        }
        foreach ($this->get_items() as $item) {
            $item->set_data($key, $value);
        }
        return $this;
    }
    /**
     * Set current page
     *
     * @param int $page
     * @return $this
     */
    public function set_cur_page($page)
    {
        $this->_cur_page = $page;
        return $this;
    }
    /**
     * Set collection page size
     *
     * @param int $size
     * @return $this
     */
    public function set_page_size($size)
    {
        $this->_page_size = $size;
        return $this;
    }
    /**
     * Set select order
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function set_order($field, $direction = self::SORT_ORDER_DESC)
    {
        $this->_orders[$field] = $direction;
        return $this;
    }
    /**
     * Set collection item class name
     *
     * @param string $className
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set_item_object_class($class_name)
    {
        if (!is_a($class_name, Data_Object::class, true)) {
            throw new \InvalidArgumentException($class_name . ' does not extend \Magento\Framework\DataObject');
        }
        $this->_item_object_class = $class_name;
        return $this;
    }
    /**
     * Retrieve collection empty item
     *
     * @return DataObject
     */
    public function get_new_empty_item()
    {
        return $this->_entity_factory->create($this->_item_object_class);
    }
    /**
     * Render sql select conditions
     *
     * @return $this
     */
    protected function _render_filters()
    {
        return $this;
    }
    /**
     * Render sql select orders
     *
     * @return $this
     */
    protected function _render_orders()
    {
        return $this;
    }
    /**
     * Render sql select limit
     *
     * @return $this
     */
    protected function _render_limit()
    {
        return $this;
    }
    /**
     * Set select distinct
     *
     * @param bool $flag
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function distinct($flag)
    {
        return $this;
    }
    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load_data($print_query = false, $log_query = false)
    {
        return $this;
    }
    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($print_query = false, $log_query = false)
    {
        return $this->load_data($print_query, $log_query);
    }
    /**
     * Load data with filter in place
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load_with_filter($print_query = false, $log_query = false)
    {
        return $this->load_data($print_query, $log_query);
    }
    /**
     * Convert collection to XML
     *
     * @return string
     */
    public function to_xml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <collection>
           <totalRecords>' . $this->_total_records . '</totalRecords>
           <items>';
        foreach ($this as $item) {
            $xml .= $item->to_xml();
        }
        $xml .= '</items>
        </collection>';
        return $xml;
    }
    /**
     * Convert collection to array
     *
     * @param array $arrRequiredFields
     * @return array
     */
    public function to_array($arr_required_fields = [])
    {
        $arr_items = [];
        $arr_items['totalRecords'] = $this->get_size();
        $arr_items['items'] = [];
        foreach ($this as $item) {
            $arr_items['items'][] = $item->to_array($arr_required_fields);
        }
        return $arr_items;
    }
    /**
     * Convert items array to array for select options
     *
     * Return items array
     * array(
     *      $index => array(
     *          'value' => string
     *          'label' => string
     *      )
     * )
     *
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _to_option_array($value_field = 'id', $label_field = 'name', $additional = [])
    {
        $res = [];
        $additional['value'] = $value_field;
        $additional['label'] = $label_field;
        foreach ($this as $item) {
            foreach ($additional as $code => $field) {
                $data[$code] = $item->get_data($field);
            }
            $res[] = $data;
        }
        return $res;
    }
    /**
     * Returns option array
     *
     * @return array
     */
    public function to_option_array()
    {
        return $this->_to_option_array();
    }
    /**
     * Returns options hash
     *
     * @return array
     */
    public function to_option_hash()
    {
        return $this->_to_option_hash();
    }
    /**
     * Convert items array to hash for select options
     *
     * Return items hash
     * array($value => $label)
     *
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    protected function _to_option_hash($value_field = 'id', $label_field = 'name')
    {
        $res = [];
        foreach ($this as $item) {
            $res[$item->get_data($value_field)] = $item->get_data($label_field);
        }
        return $res;
    }
    /**
     * Retrieve item by id
     *
     * @param string|int $idValue
     * @return DataObject|null
     */
    public function get_item_by_id($id_value)
    {
        $id_value = $id_value ?? '';
        $this->load();
        if (isset($this->_items[$id_value])) {
            return $this->_items[$id_value];
        }
        return null;
    }
    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    #[\Return_Type_Will_Change]
    public function getIterator()
    {
        $this->load();
        return new \ArrayIterator($this->_items);
    }
    /**
     * Retrieve count of collection loaded items
     *
     * @return int
     */
    #[\Return_Type_Will_Change]
    public function count()
    {
        $this->load();
        return count($this->_items);
    }
    /**
     * Retrieve Flag
     *
     * @param string $flag
     * @return bool|null
     */
    public function get_flag($flag)
    {
        return $this->_flags[$flag] ?? null;
    }
    /**
     * Set Flag
     *
     * @param string $flag
     * @param bool|null $value
     * @return $this
     */
    public function set_flag($flag, $value = null)
    {
        $this->_flags[$flag] = $value;
        return $this;
    }
    /**
     * Has Flag
     *
     * @param string $flag
     * @return bool
     */
    public function has_flag($flag)
    {
        return array_key_exists($flag, $this->_flags);
    }
    /**
     * Sleep handler
     *
     * @return string[]
     * @since 100.0.11
     */
    public function __sleep()
    {
        $properties = array_keys(get_object_vars($this));
        $properties = array_diff($properties, ['_entityFactory']);
        return $properties;
    }
    /**
     * Init not serializable fields
     *
     * @return void
     * @since 100.0.11
     */
    public function __wakeup()
    {
        // phpcs:ignore Magento2.PHP.AutogeneratedClassNotInConstructor
        $this->_entity_factory = Object_Manager::get_instance()->get(Entity_Factory_Interface::class);
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->clear();
        $this->_is_collection_loaded = null;
        $this->_orders = [];
        $this->_filters = [];
        $this->_is_filters_rendered = false;
        $this->_cur_page = 1;
        $this->_page_size = false;
        $this->_flags = [];
    }
}