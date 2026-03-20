<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\Api\Criteria_Interface;
use Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface;
use Magento\Framework\Data\Object_Factory;
use Magento\Framework\DB\Adapter\Adapter_Interface;
use Psr\Log\Logger_Interface as Logger;
/**
 * Class AbstractMapper
 * @package Magento\Framework\DB
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Abstract_Mapper implements Mapper_Interface
{
    /**
     * Resource model name
     *
     * @var string
     */
    protected $resource_model;
    /**
     * Resource instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $resource;
    /**
     * Store joined tables here
     *
     * @var array
     */
    protected $joined_tables = [];
    /**
     * DB connection
     *
     * @var AdapterInterface
     */
    protected $connection;
    /**
     * Select object
     *
     * @var Select
     */
    protected $select;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var FetchStrategyInterface
     */
    protected $fetch_strategy;
    /**
     * @var ObjectFactory
     */
    protected $object_factory;
    /**
     * @var MapperFactory
     */
    protected $mapper_factory;
    /**
     * Fields and filters map
     *
     * @var array
     */
    protected $map = [];
    /**
     * @param Logger $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ObjectFactory $objectFactory
     * @param MapperFactory $mapperFactory
     * @param Select $select
     */
    public function __construct(Logger $logger, Fetch_Strategy_Interface $fetch_strategy, Object_Factory $object_factory, Mapper_Factory $mapper_factory, ?Select $select = null)
    {
        $this->logger = $logger;
        $this->fetch_strategy = $fetch_strategy;
        $this->object_factory = $object_factory;
        $this->mapper_factory = $mapper_factory;
        $this->select = $select;
        $this->init();
    }
    /**
     * Set initial conditions
     *
     * @return void
     */
    abstract protected function init();
    /**
     * Map criteria to Select Query Object
     *
     * @param CriteriaInterface $criteria
     * @return Select
     */
    public function map(Criteria_Interface $criteria)
    {
        $criteria_parts = $criteria->to_array();
        foreach ($criteria_parts as $key => $value) {
            $camel_case_key = \Magento\Framework\Api\Simple_Data_Object_Converter::snake_case_to_upper_camel_case($key);
            $mapper_method = 'map' . $camel_case_key;
            if (method_exists($this, $mapper_method)) {
                if (!is_array($value)) {
                    throw new \InvalidArgumentException('Wrong type of argument, expecting array for ' . $mapper_method);
                }
                // The `array_values` is a workaround to ensure the same behavior in PHP 7 and 8.
                call_user_func_array([$this, $mapper_method], array_values($value));
            }
        }
        return $this->select;
    }
    /**
     * Add attribute expression (SUM, COUNT, etc)
     * Example: ('sub_total', 'SUM({{attribute}})', 'revenue')
     * Example: ('sub_total', 'SUM({{revenue}})', 'revenue')
     * For some functions like SUM use groupByAttribute.
     *
     * @param string $alias
     * @param string $expression
     * @param array|string $fields
     * @return void
     */
    public function add_expression_field_to_select($alias, $expression, $fields)
    {
        // validate alias
        if (!is_array($fields)) {
            $fields = [$fields => $fields];
        }
        $full_expression = $expression;
        foreach ($fields as $field_key => $field_item) {
            $field_item = $field_item !== null ? $field_item : '';
            $full_expression = $full_expression !== null ? $full_expression : '';
            $full_expression = str_replace('{{' . $field_key . '}}', $field_item, $full_expression);
        }
        $this->get_select()->columns([$alias => $full_expression]);
    }
    /**
     * @inheritdoc
     */
    public function add_field_to_filter($field, $condition = null)
    {
        if (is_array($field)) {
            $conditions = [];
            foreach ($field as $key => $value) {
                $conditions[] = $this->translate_condition($value, isset($condition[$key]) ? $condition[$key] : null);
            }
            $result_condition = '(' . implode(') ' . \Magento\Framework\DB\Select::SQL_OR . ' (', $conditions) . ')';
        } else {
            $result_condition = $this->translate_condition($field, $condition);
        }
        $this->select->where($result_condition, null, Select::TYPE_CONDITION);
    }
    /**
     * @inheritdoc
     */
    public function reset()
    {
        $this->get_select()->reset();
    }
    /**
     * Set resource model name
     *
     * @param string $model
     * @return void
     */
    protected function set_resource_model_name($model)
    {
        $this->resource_model = $model;
    }
    /**
     *  Retrieve resource model name
     *
     * @return string
     */
    protected function get_resource_model_name()
    {
        return $this->resource_model;
    }
    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function get_resource()
    {
        if (empty($this->resource)) {
            $this->resource = \Magento\Framework\App\Object_Manager::get_instance()->create($this->get_resource_model_name());
        }
        return $this->resource;
    }
    /**
     * Standard query builder initialization
     *
     * @param string $resourceInterface
     * @return void
     */
    protected function init_resource($resource_interface)
    {
        $this->set_resource_model_name($resource_interface);
        $this->set_connection($this->get_resource()->get_connection());
        if (!$this->select) {
            $this->select = $this->get_connection()->select();
            $this->init_select();
        }
    }
    /**
     * Init collection select
     *
     * @return void
     */
    protected function init_select()
    {
        $this->get_select()->from(['main_table' => $this->get_resource()->get_main_table()]);
    }
    /**
     * Join table to collection select
     *
     * @param string $table
     * @param string $condition
     * @param string $cols
     * @return void
     */
    protected function join($table, $condition, $cols = '*')
    {
        if (is_array($table)) {
            foreach ($table as $k => $v) {
                $alias = $k;
                $table = $v;
                break;
            }
        } else {
            $alias = $table;
        }
        if (!isset($this->joined_tables[$table])) {
            $this->get_select()->join([$alias => $this->get_table($table)], $condition, $cols);
            $this->joined_tables[$alias] = true;
        }
    }
    /**
     * Retrieve connection object
     *
     * @return AdapterInterface
     */
    protected function get_connection()
    {
        return $this->connection;
    }
    /**
     * Set database connection adapter
     *
     * @param AdapterInterface $connection
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function set_connection($connection)
    {
        if (!$connection instanceof \Magento\Framework\DB\Adapter\Adapter_Interface) {
            throw new \InvalidArgumentException((string) new \Magento\Framework\Phrase('dbModel read resource does not implement \Magento\Framework\DB\Adapter\AdapterInterface'));
        }
        $this->connection = $connection;
    }
    /**
     * Build sql where condition part
     *
     * @param   string|array $field
     * @param   null|string|array $condition
     * @return  string
     */
    protected function translate_condition($field, $condition)
    {
        $field = $this->get_mapped_field($field);
        return $this->get_condition_sql($this->get_connection()->quote_identifier($field), $condition);
    }
    /**
     * Try to get mapped field name for filter to collection
     *
     * @param   string $field
     * @return  string
     */
    protected function get_mapped_field($field)
    {
        $mapper = $this->get_mapper();
        if (isset($mapper['fields'][$field])) {
            $mapped_field = $mapper['fields'][$field];
        } else {
            $mapped_field = $field;
        }
        return $mapped_field;
    }
    /**
     * Retrieve mapper data
     *
     * @return array|bool|null
     */
    protected function get_mapper()
    {
        if (isset($this->map)) {
            return $this->map;
        } else {
            return false;
        }
    }
    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * - array("from" => $fromValue, "to" => $toValue)
     * - array("eq" => $equalValue)
     * - array("neq" => $notEqualValue)
     * - array("like" => $likeValue)
     * - array("in" => array($inValues))
     * - array("nin" => array($notInValues))
     * - array("notnull" => $valueIsNotNull)
     * - array("null" => $valueIsNull)
     * - array("moreq" => $moreOrEqualValue)
     * - array("gt" => $greaterValue)
     * - array("lt" => $lessValue)
     * - array("gteq" => $greaterOrEqualValue)
     * - array("lteq" => $lessOrEqualValue)
     * - array("finset" => $valueInSet)
     * - array("regexp" => $regularExpression)
     * - array("seq" => $stringValue)
     * - array("sneq" => $stringValue)
     *
     * If non matched - sequential array is expected and OR conditions
     * will be built using above mentioned structure
     *
     * @param string $fieldName
     * @param integer|string|array $condition
     * @return string
     */
    protected function get_condition_sql($field_name, $condition)
    {
        return $this->get_connection()->prepare_sql_condition($field_name, $condition);
    }
    /**
     * Return the field name for the condition.
     *
     * @param string $fieldName
     * @return string
     */
    protected function get_condition_field_name($field_name)
    {
        return $field_name;
    }
    /**
     * Hook for operations before rendering filters
     *
     * @return void
     */
    protected function render_filters_before()
    {
    }
    /**
     * Retrieve table name
     *
     * @param string $table
     * @return string
     */
    protected function get_table($table)
    {
        return $this->get_resource()->get_table($table);
    }
    /**
     * Get \Magento\Framework\DB\Select object instance
     *
     * @return Select
     */
    protected function get_select()
    {
        return $this->select;
    }
}