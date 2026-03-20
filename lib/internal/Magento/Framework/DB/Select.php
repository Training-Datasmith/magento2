<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\DB\Sql\Expression;
/**
 * Class for SQL SELECT generation and results.
 *
 * @api
 * @method \Magento\Framework\DB\Select from($name, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select join($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinInner($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinLeft($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinNatural($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinFull($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinRight($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinCross($name, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select orWhere($cond, $value = null, $type = null)
 * @method \Magento\Framework\DB\Select group($spec)
 * @method \Magento\Framework\DB\Select order($spec)
 * @method \Magento\Framework\DB\Select limitPage($page, $rowCount)
 * @method \Magento\Framework\DB\Select forUpdate($flag = true)
 * @method \Magento\Framework\DB\Select distinct($flag = true)
 * @method \Magento\Framework\DB\Select reset($part = null)
 * @method \Magento\Framework\DB\Select columns($cols = '*', $correlationName = null)
 * @since 100.0.2
 */
class Select extends \Zend_Db_Select
{
    /**
     * Condition type
     */
    public const TYPE_CONDITION = 'TYPE_CONDITION';
    /**
     * Straight join key
     */
    public const STRAIGHT_JOIN = 'straightjoin';
    /**
     * Straight join SQL directive.
     */
    public const SQL_STRAIGHT_JOIN = 'STRAIGHT_JOIN';
    /**
     * @var Select\SelectRenderer
     */
    private $select_renderer;
    /**
     * Class constructor
     * Add straight join support
     *
     * @param Adapter\Pdo\Mysql $adapter
     * @param Select\SelectRenderer $selectRenderer
     * @param array $parts
     */
    public function __construct(\Magento\Framework\DB\Adapter\Pdo\Mysql $adapter, \Magento\Framework\DB\Select\Select_Renderer $select_renderer, $parts = [])
    {
        self::$_parts_init = array_merge(self::$_parts_init, $parts);
        if (!isset(self::$_parts_init[self::STRAIGHT_JOIN])) {
            self::$_parts_init = [self::STRAIGHT_JOIN => false] + self::$_parts_init;
        }
        $this->select_renderer = $select_renderer;
        parent::__construct($adapter);
    }
    /**
     * Adds a WHERE condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. Array values are quoted and comma-separated.
     *
     * <code>
     * // simplest but non-secure
     * $select->where("id = $id");
     *
     * // secure (ID is quoted but matched anyway)
     * $select->where('id = ?', $id);
     *
     * // alternatively, with named binding
     * $select->where('id = :id');
     * </code>
     *
     * You may also construct IN statements:
     *
     * <code>
     * $select->where('entity_id IN (?)', ['1', '2', '3']);
     * </code>
     *
     * Note that it is more correct to use named bindings in your
     * queries for values other than strings. When you use named
     * bindings, don't forget to pass the values when actually
     * making a query:
     *
     * <code>
     * $db->fetchAll($select, array('id' => 5));
     * </code>
     *
     * @param string $cond The WHERE condition.
     * @param array|null|int|string|float|Expression|Select|\DateTimeInterface $value The value to quote.
     * @param int|string|null $type OPTIONAL SQL datatype of the given value e.g. Zend_Db::FLOAT_TYPE or "INT"
     * @return \Magento\Framework\DB\Select
     */
    public function where($cond, $value = null, $type = null)
    {
        if ($value === null && $type === null) {
            $value = '';
        } elseif ((string) $type === self::TYPE_CONDITION) {
            $type = null;
        }
        if (is_array($value)) {
            $cond = $this->get_connection()->quote_into($cond, $value, $type);
            $value = null;
        }
        return parent::where($cond, $value, $type);
    }
    /**
     * Reset unused LEFT JOIN(s)
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function reset_join_left()
    {
        foreach ($this->_parts[self::FROM] as $table_id => $table_prop) {
            if ($table_prop['joinType'] == self::LEFT_JOIN) {
                $use_join = false;
                foreach ($this->_parts[self::COLUMNS] as $column_entry) {
                    list($correlation_name, $column) = $column_entry;
                    if ($column instanceof \Zend_Db_Expr) {
                        if ($this->_find_table_in_cond($table_id, $column) || $this->_find_table_in_cond($table_prop['tableName'], $column)) {
                            $use_join = true;
                        }
                    } else if ($correlation_name == $table_id) {
                        $use_join = true;
                    }
                }
                foreach ($this->_parts[self::WHERE] as $where) {
                    if ($this->_find_table_in_cond($table_id, $where) || $this->_find_table_in_cond($table_prop['tableName'], $where)) {
                        $use_join = true;
                    }
                }
                $join_use_in_cond = $use_join;
                $join_in_tables = [];
                foreach ($this->_parts[self::FROM] as $table_correlation_name => $table) {
                    if ($table_correlation_name == $table_id) {
                        continue;
                    }
                    if (!empty($table['joinCondition'])) {
                        if ($this->_find_table_in_cond($table_id, $table['joinCondition']) || $this->_find_table_in_cond($table_prop['tableName'], $table['joinCondition'])) {
                            $use_join = true;
                            $join_in_tables[] = $table_correlation_name;
                        }
                    }
                }
                if (!$use_join) {
                    unset($this->_parts[self::FROM][$table_id]);
                } else {
                    $this->_parts[self::FROM][$table_id]['useInCond'] = $join_use_in_cond;
                    $this->_parts[self::FROM][$table_id]['joinInTables'] = $join_in_tables;
                }
            }
        }
        $this->_reset_join_left();
        return $this;
    }
    /**
     * Validate LEFT joins, and remove it if not exists
     *
     * @return $this
     */
    protected function _reset_join_left()
    {
        foreach ($this->_parts[self::FROM] as $table_id => $table_prop) {
            if ($table_prop['joinType'] == self::LEFT_JOIN) {
                if ($table_prop['useInCond']) {
                    continue;
                }
                $used = false;
                foreach ($table_prop['joinInTables'] as $table) {
                    if (isset($this->_parts[self::FROM][$table])) {
                        $used = true;
                        break;
                    }
                }
                if (!$used) {
                    unset($this->_parts[self::FROM][$table_id]);
                    return $this->_reset_join_left();
                }
            }
        }
        return $this;
    }
    /**
     * Find table name in condition (where, column)
     *
     * @param string $table
     * @param string $cond
     * @return bool
     */
    protected function _find_table_in_cond($table, $cond)
    {
        $quote = $this->_adapter->get_quote_identifier_symbol();
        $cond = (string) $cond;
        $table = (string) $table;
        if (strpos($cond, $quote . $table . $quote . '.') !== false) {
            return true;
        }
        $position = 0;
        $result = 0;
        $needle = [];
        while (is_integer($result)) {
            $result = strpos($cond, $table . '.', $position);
            if (is_integer($result)) {
                $needle[] = $result;
                $position = $result + strlen($table) + 1;
            }
        }
        if (!$needle) {
            return false;
        }
        foreach ($needle as $position) {
            if ($position == 0) {
                return true;
            }
            if (!preg_match('#[a-z0-9_]#is', substr($cond, $position - 1, 1))) {
                return true;
            }
        }
        return false;
    }
    /**
     * Populate the {@link $_parts} 'join' key
     *
     * Does the dirty work of populating the join key.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  null|string $type Type of join; inner, left, and null are currently supported
     * @param  array|string|\Zend_Db_Expr $name Table name
     * @param  string $cond Join on this condition
     * @param  array|string $cols The columns to select from the joined table
     * @param  string $schema The database name to specify, if any.
     * @return \Magento\Framework\DB\Select This \Magento\Framework\DB\Select object
     * @throws \Zend_Db_Select_Exception
     */
    protected function _join($type, $name, $cond, $cols, $schema = null)
    {
        if ($type == self::INNER_JOIN && empty($cond)) {
            $type = self::CROSS_JOIN;
        }
        return parent::_join($type, $name, $cond, $cols, $schema);
    }
    /**
     * Sets a limit count and offset to the query.
     *
     * @param int $count OPTIONAL The number of rows to return.
     * @param int $offset OPTIONAL Start returning after this many rows.
     * @return $this
     */
    public function limit($count = null, $offset = null)
    {
        if ($count === null) {
            $this->reset(self::LIMIT_COUNT);
        } else {
            $this->_parts[self::LIMIT_COUNT] = (int) $count;
        }
        if ($offset === null) {
            $this->reset(self::LIMIT_OFFSET);
        } else {
            $this->_parts[self::LIMIT_OFFSET] = (int) $offset;
        }
        return $this;
    }
    /**
     * Cross Table Update From Current select
     *
     * @param string|array $table
     * @return string
     */
    public function cross_update_from_select($table)
    {
        return $this->get_connection()->update_from_select($this, $table);
    }
    /**
     * Insert to table from current select
     *
     * @param string $tableName
     * @param array $fields
     * @param bool $onDuplicate
     * @return string
     */
    public function insert_from_select($table_name, $fields = [], $on_duplicate = true)
    {
        $mode = $on_duplicate ? Adapter_Interface::INSERT_ON_DUPLICATE : false;
        return $this->get_connection()->insert_from_select($this, $table_name, $fields, $mode);
    }
    /**
     * Generate INSERT IGNORE query to the table from current select
     *
     * @param string $tableName
     * @param array $fields
     * @return string
     */
    public function insert_ignore_from_select($table_name, $fields = [])
    {
        return $this->get_connection()->insert_from_select($this, $table_name, $fields, Adapter_Interface::INSERT_IGNORE);
    }
    /**
     * Retrieve DELETE query from select
     *
     * @param string $table The table name or alias
     * @return string
     */
    public function delete_from_select($table)
    {
        return $this->get_connection()->delete_from_select($this, $table);
    }
    /**
     * Modify (hack) part of the structured information for the current query
     *
     * @param string $part
     * @param mixed $value
     * @return $this
     * @throws \Zend_Db_Select_Exception
     */
    public function set_part($part, $value)
    {
        $part = $part !== null ? strtolower($part) : '';
        if (!array_key_exists($part, $this->_parts)) {
            throw new \Zend_Db_Select_Exception("Invalid Select part '{$part}'");
        }
        $this->_parts[$part] = $value;
        return $this;
    }
    /**
     * Use a STRAIGHT_JOIN for the SQL Select
     *
     * @param bool $flag Whether or not the SELECT use STRAIGHT_JOIN (default true).
     * @return $this
     */
    public function use_straight_join($flag = true)
    {
        $this->_parts[self::STRAIGHT_JOIN] = (bool) $flag;
        return $this;
    }
    /**
     * Render STRAIGHT_JOIN clause
     *
     * @param string $sql SQL query
     * @return string
     */
    protected function _render_straightjoin($sql)
    {
        if ($this->_adapter->support_straight_join() && !empty($this->_parts[self::STRAIGHT_JOIN])) {
            $sql .= ' ' . self::SQL_STRAIGHT_JOIN;
        }
        return $sql;
    }
    /**
     * Adds to the internal table-to-column mapping array.
     *
     * @param  string $correlationName The table/join the columns come from.
     * @param  array|string $cols The list of columns; preferably as an array,
     *     but possibly as a string containing one column.
     * @param  bool|string $afterCorrelationName True if it should be prepended,
     *     a correlation name if it should be inserted
     * @return void
     */
    protected function _table_cols($correlation_name, $cols, $after_correlation_name = null)
    {
        if (!is_array($cols)) {
            $cols = [$cols];
        }
        foreach ($cols as $k => $v) {
            if ($v instanceof Select) {
                $cols[$k] = new \Zend_Db_Expr(sprintf('(%s)', $v->assemble()));
            }
        }
        parent::_table_cols($correlation_name, $cols, $after_correlation_name);
    }
    /**
     * Adds the random order to query
     *
     * @param string $field     integer field name
     * @return $this
     */
    public function order_rand($field = null)
    {
        $this->_adapter->order_rand($this, $field);
        return $this;
    }
    /**
     * Render FOR UPDATE clause
     *
     * @param string $sql SQL query
     * @return string
     */
    protected function _render_forupdate($sql)
    {
        if ($this->_parts[self::FOR_UPDATE]) {
            $sql = $this->_adapter->for_update($sql);
        }
        return $sql;
    }
    /**
     * Add EXISTS clause
     *
     * @param Select $select
     * @param string $joinCondition
     * @param bool $isExists
     * @return $this
     */
    public function exists($select, $join_condition, $is_exists = true)
    {
        if ($is_exists) {
            $exists = 'EXISTS (%s)';
        } else {
            $exists = 'NOT EXISTS (%s)';
        }
        $select->reset(self::COLUMNS)->columns([new \Zend_Db_Expr('1')])->where($join_condition);
        $exists = sprintf($exists, $select->assemble());
        $this->where($exists);
        return $this;
    }
    /**
     * Get adapter
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function get_connection()
    {
        return $this->_adapter;
    }
    /**
     * Converts this object to an SQL SELECT string.
     *
     * @return string|null This object as a SELECT string. (or null if a string cannot be produced.)
     * @since 100.1.0
     */
    public function assemble()
    {
        return $this->select_renderer->render($this);
    }
    /**
     * Remove links to other objects.
     *
     * @return string[]
     * @since 100.0.11
     */
    public function __sleep()
    {
        $properties = array_keys(get_object_vars($this));
        $properties = array_diff($properties, ['_adapter', 'selectRenderer']);
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
        $object_manager = \Magento\Framework\App\Object_Manager::get_instance();
        $this->_adapter = $object_manager->get(Resource_Connection::class)->get_connection();
        $this->select_renderer = $object_manager->get(\Magento\Framework\DB\Select\Select_Renderer::class);
    }
}