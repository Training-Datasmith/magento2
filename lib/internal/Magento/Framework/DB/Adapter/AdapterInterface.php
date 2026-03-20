<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Adapter;

use Magento\Framework\DB\Ddl\Table;
/**
 * Magento Database Adapter Interface
 *
 * @api
 * @since 100.0.2
 */
interface Adapter_Interface
{
    public const INDEX_TYPE_PRIMARY = 'primary';
    public const INDEX_TYPE_UNIQUE = 'unique';
    public const INDEX_TYPE_INDEX = 'index';
    public const INDEX_TYPE_FULLTEXT = 'fulltext';
    public const FK_ACTION_CASCADE = 'CASCADE';
    public const FK_ACTION_SET_NULL = 'SET NULL';
    public const FK_ACTION_NO_ACTION = 'NO ACTION';
    public const FK_ACTION_RESTRICT = 'RESTRICT';
    public const FK_ACTION_SET_DEFAULT = 'SET DEFAULT';
    public const INSERT_ON_DUPLICATE = 1;
    public const INSERT_IGNORE = 2;
    /** Strategy for updating data in table. See https://dev.mysql.com/doc/refman/5.7/en/replace.html */
    public const REPLACE = 4;
    public const ISO_DATE_FORMAT = 'yyyy-MM-dd';
    public const ISO_DATETIME_FORMAT = 'yyyy-MM-dd HH-mm-ss';
    public const INTERVAL_SECOND = 'SECOND';
    public const INTERVAL_MINUTE = 'MINUTES';
    public const INTERVAL_HOUR = 'HOURS';
    public const INTERVAL_DAY = 'DAYS';
    public const INTERVAL_MONTH = 'MONTHS';
    public const INTERVAL_YEAR = 'YEARS';
    /**
     * Error message for DDL query in transactions
     */
    public const ERROR_DDL_MESSAGE = 'DDL statements are not allowed in transactions';
    /**
     * Error message for unfinished rollBack transaction
     */
    public const ERROR_ROLLBACK_INCOMPLETE_MESSAGE = 'Rolled back transaction has not been completed correctly.';
    /**
     * Error message for asymmetric transaction rollback
     */
    public const ERROR_ASYMMETRIC_ROLLBACK_MESSAGE = 'Asymmetric transaction rollback.';
    /**
     * Error message for asymmetric transaction commit
     */
    public const ERROR_ASYMMETRIC_COMMIT_MESSAGE = 'Asymmetric transaction commit.';
    /**
     * Begin new DB transaction for connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function begin_transaction();
    /**
     * Commit DB transaction
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function commit();
    /**
     * Roll-back DB transaction
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function roll_back();
    /**
     * Retrieve DDL object for new table
     *
     * @param string $tableName the table name
     * @param string $schemaName the database or schema name
     * @return Table
     */
    public function new_table($table_name = null, $schema_name = null);
    /**
     * Create table from DDL object
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Interface
     */
    public function create_table(Table $table);
    /**
     * Drop table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function drop_table($table_name, $schema_name = null);
    /**
     * Create temporary table from DDL object
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Interface
     */
    public function create_temporary_table(Table $table);
    /**
     * Create temporary table from other table
     *
     * @param string $temporaryTableName
     * @param string $originTableName
     * @param bool $ifNotExists
     * @return \Zend_Db_Statement_Interface
     */
    public function create_temporary_table_like($temporary_table_name, $origin_table_name, $if_not_exists = false);
    /**
     * Drop temporary table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function drop_temporary_table($table_name, $schema_name = null);
    /**
     * Rename several tables
     *
     * @param array $tablePairs array('oldName' => 'Name1', 'newName' => 'Name2')
     *
     * @return boolean
     * @throws \Zend_Db_Exception
     */
    public function rename_tables_batch(array $table_pairs);
    /**
     * Truncate a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function truncate_table($table_name, $schema_name = null);
    /**
     * Checks if table exists
     *
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function is_table_exists($table_name, $schema_name = null);
    /**
     * Returns short table status array
     *
     * @param string $tableName
     * @param string $schemaName
     * @return array|false
     */
    public function show_table_status($table_name, $schema_name = null);
    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describe_table($table_name, $schema_name = null);
    /**
     * Create \Magento\Framework\DB\Ddl\Table object by data from describe table
     *
     * @param string $tableName
     * @param string $newTableName
     * @return Table
     */
    public function create_table_by_ddl($table_name, $new_table_name);
    /**
     * Modify the column definition by data from describe table
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function modify_column_by_ddl($table_name, $column_name, $definition, $flush_data = false, $schema_name = null);
    /**
     * Rename table
     *
     * @param string $oldTableName
     * @param string $newTableName
     * @param string $schemaName
     * @return boolean
     */
    public function rename_table($old_table_name, $new_table_name, $schema_name = null);
    /**
     * Adds new column to the table.
     *
     * Generally $defintion must be array with column data to keep this call cross-DB compatible.
     * Using string as $definition is allowed only for concrete DB adapter.
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition string specific or universal array DB Server definition
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function add_column($table_name, $column_name, $definition, $schema_name = null);
    /**
     * Change the column name and definition
     *
     * For change definition of column - use modifyColumn
     *
     * @param string $tableName
     * @param string $oldColumnName
     * @param string $newColumnName
     * @param array|string $definition
     * @param boolean $flushData flush table statistic
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function change_column($table_name, $old_column_name, $new_column_name, $definition, $flush_data = false, $schema_name = null);
    /**
     * Modify the column definition
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function modify_column($table_name, $column_name, $definition, $flush_data = false, $schema_name = null);
    /**
     * Drop the column from table
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return boolean
     */
    public function drop_column($table_name, $column_name, $schema_name = null);
    /**
     * Check is table column exists
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return boolean
     */
    public function table_column_exists($table_name, $column_name, $schema_name = null);
    /**
     * Add new index to table name
     *
     * @param string $tableName
     * @param string $indexName
     * @param string|array $fields the table column name or array of ones
     * @param string $indexType the index type
     * @param string $schemaName
     * @return \Zend_Db_Statement_Interface
     */
    public function add_index($table_name, $index_name, $fields, $index_type = self::INDEX_TYPE_INDEX, $schema_name = null);
    /**
     * Drop the index from table
     *
     * @param string $tableName
     * @param string $keyName
     * @param string $schemaName
     * @return bool|\Zend_Db_Statement_Interface
     */
    public function drop_index($table_name, $key_name, $schema_name = null);
    /**
     * Returns the table index information
     *
     * The return value is an associative array keyed by the UPPERCASE index key (except for primary key,
     * that is always stored under 'PRIMARY' key) as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string; name of the table
     * KEY_NAME         => string; the original index name
     * COLUMNS_LIST     => array; array of index column names
     * INDEX_TYPE       => string; lowercase, create index type
     * INDEX_METHOD     => string; index method using
     * type             => string; see INDEX_TYPE
     * fields           => array; see COLUMNS_LIST
     *
     * @param string $tableName
     * @param string $schemaName
     * @return array
     */
    public function get_index_list($table_name, $schema_name = null);
    /**
     * Add new Foreign Key to table
     *
     * If Foreign Key with same name is exist - it will be deleted
     *
     * @param string $fkName
     * @param string $tableName
     * @param string $columnName
     * @param string $refTableName
     * @param string $refColumnName
     * @param string $onDelete
     * @param boolean $purge trying remove invalid data
     * @param string $schemaName
     * @param string $refSchemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function add_foreign_key($fk_name, $table_name, $column_name, $ref_table_name, $ref_column_name, $on_delete = self::FK_ACTION_CASCADE, $purge = false, $schema_name = null, $ref_schema_name = null);
    /**
     * Drop the Foreign Key from table
     *
     * @param string $tableName
     * @param string $fkName
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function drop_foreign_key($table_name, $fk_name, $schema_name = null);
    /**
     * Retrieve the foreign keys descriptions for a table.
     *
     * The return value is an associative array keyed by the UPPERCASE foreign key,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * FK_NAME          => string; original foreign key name
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * REF_SCHEMA_NAME  => string; name of reference database or schema
     * REF_TABLE_NAME   => string; reference table name
     * REF_COLUMN_NAME  => string; reference column name
     * ON_DELETE        => string; action type on delete row
     * ON_UPDATE        => string; action type on update row
     *
     * @param string $tableName
     * @param string $schemaName
     * @return array
     */
    public function get_foreign_keys($table_name, $schema_name = null);
    /**
     * Creates and returns a new \Magento\Framework\DB\Select object for this adapter.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function select();
    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     */
    public function insert_on_duplicate($table, array $data, array $fields = []);
    /**
     * Inserts a table multiply rows with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $data Column-value pairs or array of Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert_multiple($table, array $data);
    /**
     * Insert array into a table based on columns definition
     *
     * $data can be represented as:
     * - arrays of values ordered according to columns in $columns array
     *      array(
     *          array('value1', 'value2'),
     *          array('value3', 'value4'),
     *      )
     * - array of values, if $columns contains only one column
     *      array('value1', 'value2')
     *
     * @param   string $table
     * @param   string[] $columns the data array column map
     * @param   array $data
     * @return  int
     */
    public function insert_array($table, array $columns, array $data);
    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind);
    /**
     * Inserts a table row with specified data
     *
     * Special for Zero values to identity column
     *
     * @param string $table
     * @param array $bind
     * @return int The number of affected rows.
     */
    public function insert_force($table, array $bind);
    /**
     * Updates table rows with specified data based on a WHERE clause.
     *
     * The $where parameter in this instance can be a single WHERE clause or an array containing a multiple.  In all
     * instances, a WHERE clause can be a string or an instance of {@see Zend_Db_Expr}.  In the event you use an array,
     * you may specify the clause as the key and a value to be bound to it as the value. E.g., ['amt > ?' => $amt]
     *
     * If the $where parameter is an array of multiple clauses, they will be joined by AND, with each clause wrapped in
     * parenthesis.  If you wish to use an OR, you must give a single clause that is an instance of {@see Zend_Db_Expr}
     *
     * @param  mixed $table The table to update.
     * @param  array $bind Column-value pairs.
     * @param  mixed $where UPDATE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function update($table, array $bind, $where = '');
    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed $table The table to update.
     * @param  mixed $where DELETE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function delete($table, $where = '');
    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param  mixed $sql The SQL statement with placeholders.
     *                      May be a string or \Magento\Framework\DB\Select.
     * @param  mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Interface
     */
    public function query($sql, $bind = []);
    /**
     * Fetches all SQL result rows as a sequential array.
     *
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @param mixed $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetch_all($sql, $bind = [], $fetch_mode = null);
    /**
     * Fetches the first row of the SQL result.
     *
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @param mixed $fetchMode Override current fetch mode.
     * @return mixed Array, object, or scalar depending on fetch mode.
     */
    public function fetch_row($sql, $bind = [], $fetch_mode = null);
    /**
     * Fetches all SQL result rows as an associative array.
     *
     * The first column is the key, the entire row array is the
     * value.  You should construct the query to be sure that
     * the first column contains unique values, or else
     * rows with duplicate values in the first column will
     * overwrite previous data.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetch_assoc($sql, $bind = []);
    /**
     * Fetches the first column of all SQL result rows as an array.
     *
     * The first column in each row is used as the array key.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetch_col($sql, $bind = []);
    /**
     * Fetches all SQL result rows as an array of key-value pairs.
     *
     * The first column is the key, the second column is the
     * value.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetch_pairs($sql, $bind = []);
    /**
     * Fetches the first column of the first row of the SQL result.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return string
     */
    public function fetch_one($sql, $bind = []);
    /**
     * Safely quotes a value for an SQL statement.
     *
     * If an array is passed as the value, the array values are quoted
     * and then returned as a comma-separated string.
     *
     * @param mixed $value The value to quote.
     * @param mixed $type  OPTIONAL the SQL datatype name, or constant, or null.
     * @return mixed An SQL-safe quoted value (or string of separated values).
     */
    public function quote($value, $type = null);
    /**
     * Quotes a value and places into a piece of text at a placeholder.
     *
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.   For example:
     *
     * <code>
     * $text = "WHERE date < ?";
     * $date = "2005-01-02";
     * $safe = $sql->quoteInto($text, $date);
     * // $safe = "WHERE date < '2005-01-02'"
     * </code>
     *
     * @param string $text The text with a placeholder.
     * @param mixed $value The value to quote.
     * @param string $type OPTIONAL SQL datatype
     * @param integer $count OPTIONAL count of placeholders to replace
     * @return string An SQL-safe quoted value placed into the original text.
     */
    public function quote_into($text, $value, $type = null, $count = null);
    /**
     * Quotes an identifier.
     *
     * Accepts a string representing a qualified identifier. For Example:
     * <code>
     * $adapter->quoteIdentifier('myschema.mytable')
     * </code>
     * Returns: "myschema"."mytable"
     *
     * Or, an array of one or more identifiers that may form a qualified identifier:
     * <code>
     * $adapter->quoteIdentifier(array('myschema','my.table'))
     * </code>
     * Returns: "myschema"."my.table"
     *
     * The actual quote character surrounding the identifiers may vary depending on
     * the adapter.
     *
     * @param string|array|\Zend_Db_Expr $ident The identifier.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier.
     */
    public function quote_identifier($ident, $auto = false);
    /**
     * Quote a column identifier and alias.
     *
     * @param string|array|\Zend_Db_Expr $ident The identifier or expression.
     * @param string|null $alias An alias for the column.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    public function quote_column_as($ident, $alias, $auto = false);
    /**
     * Quote a table identifier and alias.
     *
     * @param string|array|\Zend_Db_Expr $ident The identifier or expression.
     * @param string $alias An alias for the table.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    public function quote_table_as($ident, $alias = null, $auto = false);
    /**
     * Format Date to internal database date format
     *
     * @param int|string|\DateTimeInterface $date
     * @param boolean $includeTime
     * @return \Zend_Db_Expr
     */
    public function format_date($date, $include_time = true);
    /**
     * Run additional environment before setup
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function start_setup();
    /**
     * Run additional environment after setup
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function end_setup();
    /**
     * Set cache adapter
     *
     * @param \Magento\Framework\Cache\FrontendInterface $cacheAdapter
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function set_cache_adapter(\Magento\Framework\Cache\Frontend_Interface $cache_adapter);
    /**
     * Allow DDL caching
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function allow_ddl_cache();
    /**
     * Disallow DDL caching
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function disallow_ddl_cache();
    /**
     * Reset cached DDL data from cache
     *
     * If table name is null - reset all cached DDL data
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function reset_ddl_cache($table_name = null, $schema_name = null);
    /**
     * Save DDL data into cache
     *
     * @param string $tableCacheKey
     * @param int $ddlType
     * @param mixed $data
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function save_ddl_cache($table_cache_key, $ddl_type, $data);
    /**
     * Load DDL data from cache
     *
     * Return false if cache does not exists
     *
     * @param string $tableCacheKey the table cache key
     * @param int $ddlType the DDL constant
     * @return string|array|int|false
     */
    public function load_ddl_cache($table_cache_key, $ddl_type);
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
    public function prepare_sql_condition($field_name, $condition);
    /**
     * Prepare value for save in column
     *
     * Return converted to column data type value
     *
     * @param array $column     the column describe array
     * @param mixed $value
     * @return mixed
     */
    public function prepare_column_value(array $column, $value);
    /**
     * Generate fragment of SQL, that check condition and return true or false value
     *
     * @param string $condition     expression
     * @param string $true          true value
     * @param string $false         false value
     * @return \Zend_Db_Expr
     */
    public function get_check_sql($condition, $true, $false);
    /**
     * Returns valid IFNULL expression
     *
     * @param string $expression
     * @param string|int $value OPTIONAL. Applies when $expression is NULL
     * @return \Zend_Db_Expr
     */
    public function get_if_null_sql($expression, $value = 0);
    /**
     * Generate fragment of SQL, that combine together (concatenate) the results from data array
     *
     * All arguments in data must be quoted
     *
     * @param array $data
     * @param string $separator concatenate with separator
     * @return \Zend_Db_Expr
     */
    public function get_concat_sql(array $data, $separator = null);
    /**
     * Generate fragment of SQL that returns length of character string
     *
     * The string argument must be quoted
     *
     * @param string $string
     * @return \Zend_Db_Expr
     */
    public function get_length_sql($string);
    /**
     * Generate fragment of SQL, that compare with two or more arguments, and returns the smallest
     * (minimum-valued) argument
     * All arguments in data must be quoted
     *
     * @param array $data
     * @return \Zend_Db_Expr
     */
    public function get_least_sql(array $data);
    /**
     * Generate fragment of SQL, that compare with two or more arguments, and returns the largest
     * (maximum-valued) argument
     * All arguments in data must be quoted
     *
     * @param array $data
     * @return \Zend_Db_Expr
     */
    public function get_greatest_sql(array $data);
    /**
     * Add time values (intervals) to a date value
     *
     * @see INTERVAL_* constants for $unit
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param int $interval
     * @param string $unit
     * @return \Zend_Db_Expr
     */
    public function get_date_add_sql($date, $interval, $unit);
    /**
     * Subtract time values (intervals) to a date value
     *
     * @see INTERVAL_* constants for $unit
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param int|string $interval
     * @param string $unit
     * @return \Zend_Db_Expr
     */
    public function get_date_sub_sql($date, $interval, $unit);
    /**
     * Format date as specified
     *
     * Supported format Specifier
     *
     * %H   Hour (00..23)
     * %i   Minutes, numeric (00..59)
     * %s   Seconds (00..59)
     * %d   Day of the month, numeric (00..31)
     * %m   Month, numeric (00..12)
     * %Y   Year, numeric, four digits
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param string $format
     * @return \Zend_Db_Expr
     */
    public function get_date_format_sql($date, $format);
    /**
     * Extract the date part of a date or datetime expression
     *
     * @param \Zend_Db_Expr|string $date   quoted field name or SQL statement
     * @return \Zend_Db_Expr
     */
    public function get_date_part_sql($date);
    /**
     * Prepare substring sql function
     *
     * @param \Zend_Db_Expr|string $stringExpression quoted field name or SQL statement
     * @param int|string|\Zend_Db_Expr $pos
     * @param int|string|\Zend_Db_Expr|null $len
     * @return \Zend_Db_Expr
     */
    public function get_substring_sql($string_expression, $pos, $len = null);
    /**
     * Prepare standard deviation sql function
     *
     * @param \Zend_Db_Expr|string $expressionField   quoted field name or SQL statement
     * @return \Zend_Db_Expr
     */
    public function get_standard_deviation_sql($expression_field);
    /**
     * Extract part of a date
     *
     * @see INTERVAL_* constants for $unit
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param string $unit
     * @return \Zend_Db_Expr
     */
    public function get_date_extract_sql($date, $unit);
    /**
     * Retrieve valid table name
     *
     * Check table name length and allowed symbols
     *
     * @param string $tableName
     * @return string
     */
    public function get_table_name($table_name);
    /**
     * Build a trigger name based on table name and trigger details
     *
     * @param string $tableName The table that is the subject of the trigger
     * @param string $time Either "before" or "after"
     * @param string $event The DB level event which activates the trigger, i.e. "update" or "insert"
     * @return string
     */
    public function get_trigger_name($table_name, $time, $event);
    /**
     * Retrieve valid index name
     *
     * Check index name length and allowed symbols
     *
     * @param string $tableName
     * @param string|array $fields the columns list
     * @param string $indexType
     * @return string
     */
    public function get_index_name($table_name, $fields, $index_type = '');
    /**
     * Retrieve valid foreign key name
     *
     * Check foreign key name length and allowed symbols
     *
     * @param string $priTableName
     * @param string $priColumnName
     * @param string $refTableName
     * @param string $refColumnName
     * @return string
     */
    public function get_foreign_key_name($pri_table_name, $pri_column_name, $ref_table_name, $ref_column_name);
    /**
     * Stop updating indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function disable_table_keys($table_name, $schema_name = null);
    /**
     * Re-create missing indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function enable_table_keys($table_name, $schema_name = null);
    /**
     * Get insert from Select object query
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $table insert into table
     * @param array $fields
     * @param int|bool $mode
     * @return string
     */
    public function insert_from_select(\Magento\Framework\DB\Select $select, $table, array $fields = [], $mode = false);
    /**
     * Get insert queries in array for insert by range with step parameter
     *
     * @param string $rangeField
     * @param \Magento\Framework\DB\Select $select
     * @param int $stepCount
     * @return \Magento\Framework\DB\Select[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function selects_by_range($range_field, \Magento\Framework\DB\Select $select, $step_count = 100);
    /**
     * Get update table query using select object for join and update
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string|array $table
     * @return string
     */
    public function update_from_select(\Magento\Framework\DB\Select $select, $table);
    /**
     * Get delete from select object query
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $table the table name or alias used in select
     * @return string|int
     */
    public function delete_from_select(\Magento\Framework\DB\Select $select, $table);
    /**
     * Return array of table(s) checksum as table name - checksum pairs
     *
     * @param array|string $tableNames
     * @param string $schemaName
     * @return array
     */
    public function get_tables_checksum($table_names, $schema_name = null);
    /**
     * Check if the database support STRAIGHT JOIN
     *
     * @return boolean
     */
    public function support_straight_join();
    /**
     * Adds order by random to select object
     *
     * Possible using integer field for optimization
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $field
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function order_rand(\Magento\Framework\DB\Select $select, $field = null);
    /**
     * Render SQL FOR UPDATE clause
     *
     * @param string $sql
     * @return string
     */
    public function for_update($sql);
    /**
     * Try to find installed primary key name, if not - formate new one.
     *
     * @param string $tableName Table name
     * @param string $schemaName OPTIONAL
     * @return string Primary Key name
     */
    public function get_primary_key_name($table_name, $schema_name = null);
    /**
     * Converts fetched blob into raw binary PHP data.
     *
     * Some DB drivers return blobs as hex-coded strings, so we need to process them.
     *
     * @param mixed $value
     * @return mixed
     */
    public function decode_varbinary($value);
    /**
     * Get adapter transaction level state. Return 0 if all transactions are complete
     *
     * @return int
     */
    public function get_transaction_level();
    /**
     * Create trigger
     *
     * @param \Magento\Framework\DB\Ddl\Trigger $trigger
     * @return \Zend_Db_Statement_Pdo
     */
    public function create_trigger(\Magento\Framework\DB\Ddl\Trigger $trigger);
    /**
     * Drop trigger from database
     *
     * @param string $triggerName
     * @param string|null $schemaName
     * @return bool
     */
    public function drop_trigger($trigger_name, $schema_name = null);
    /**
     * Retrieve tables list
     *
     * @param null|string $likeCondition
     * @return array
     */
    public function get_tables($like_condition = null);
    /**
     * Generates case SQL fragment
     *
     * Generate fragment of SQL, that check value against multiple condition cases
     * and return different result depends on them
     *
     * @param string $valueName Name of value to check
     * @param array $casesResults Cases and results
     * @param string $defaultValue value to use if value doesn't confirm to any cases
     * @return \Zend_Db_Expr
     */
    public function get_case_sql($value_name, $cases_results, $default_value = null);
    /**
     * Returns auto increment field if exists
     *
     * @param string $tableName
     * @param string|null $schemaName
     * @return string|bool
     * @since 100.1.0
     */
    public function get_auto_increment_field($table_name, $schema_name = null);
}