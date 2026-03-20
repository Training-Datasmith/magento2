<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Ddl;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Factories\Table as DtoTable;
/**
 * Data Definition for table
 *
 * @api
 * @since 100.0.2
 */
class Table
{
    /**
     * Types of columns
     */
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_SMALLINT = 'smallint';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BIGINT = 'bigint';
    public const TYPE_FLOAT = 'float';
    public const TYPE_NUMERIC = 'numeric';
    public const TYPE_DECIMAL = 'decimal';
    public const TYPE_DATE = 'date';
    public const TYPE_TIMESTAMP = 'timestamp';
    // Capable to support date-time from 1970 + auto-triggers in some RDBMS
    public const TYPE_DATETIME = 'datetime';
    // Capable to support long date-time before 1970
    public const TYPE_TEXT = 'text';
    // A real blob, stored as binary inside DB
    public const TYPE_BLOB = 'blob';
    // Used for back compatibility, when query param can't use statement options
    public const TYPE_VARBINARY = 'varbinary';
    /**
     * Default and maximal TEXT and BLOB columns sizes we can support for different DB systems.
     */
    public const DEFAULT_TEXT_SIZE = 1024;
    public const MAX_TEXT_SIZE = 2147483648;
    public const MAX_VARBINARY_SIZE = 2147483648;
    /**
     * Default values for timestamps - fill with current timestamp on inserting record, on changing and both cases
     */
    public const TIMESTAMP_INIT_UPDATE = 'TIMESTAMP_INIT_UPDATE';
    public const TIMESTAMP_INIT = 'TIMESTAMP_INIT';
    public const TIMESTAMP_UPDATE = 'TIMESTAMP_UPDATE';
    /**
     * Actions used for foreign keys
     */
    public const ACTION_CASCADE = 'CASCADE';
    public const ACTION_SET_NULL = 'SET NULL';
    public const ACTION_NO_ACTION = 'NO ACTION';
    public const ACTION_RESTRICT = 'RESTRICT';
    public const ACTION_SET_DEFAULT = 'SET DEFAULT';
    /**
     * Column option 'default'
     *
     * @var string
     */
    public const OPTION_DEFAULT = 'default';
    /**
     * Column option 'identity'
     *
     * @var string
     */
    public const OPTION_IDENTITY = 'identity';
    /**
     * Column option 'length'
     *
     * @var string
     */
    public const OPTION_LENGTH = 'length';
    /**
     * Column option 'nullable'
     *
     * @var string
     */
    public const OPTION_NULLABLE = 'nullable';
    /**
     * Column option 'precision'
     *
     * @var string
     */
    public const OPTION_PRECISION = 'precision';
    /**
     * Column option 'primary'
     *
     * @var string
     */
    public const OPTION_PRIMARY = 'primary';
    /**
     * Column option 'scale'
     *
     * @var string
     */
    public const OPTION_SCALE = 'scale';
    /**
     * Column option 'type'
     *
     * @var string
     */
    public const OPTION_TYPE = 'type';
    /**
     * Column option 'unsigned'
     *
     * @var string
     */
    public const OPTION_UNSIGNED = 'unsigned';
    /**
     * Name of table
     *
     * @var string
     */
    protected $_table_name;
    /**
     * @var string
     */
    protected $_schema_name;
    /**
     * Comment for Table
     *
     * @var string
     */
    protected $_table_comment;
    /**
     * Column descriptions for a table
     *
     * Is an associative array keyed by the uppercase column name
     * The value of each array element is an associative array
     * with the following keys:
     *
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; constant datatype of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR/INT
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     * COMMENT          => string; column description
     *
     * @var array
     */
    protected $_columns = [];
    /**
     * Index descriptions for a table
     *
     * Is an associative array keyed by the uppercase index name
     * The value of each array element is an associative array
     * with the following keys:
     *
     * INDEX_NAME       => string; index name
     * COLUMNS          => array; array of index columns
     * TYPE             => string; Optional special index type
     *
     * COLUMNS is an associative array keyed by the uppercase column name
     * The value of each COLUMNS array element is an associative array
     * with the following keys:
     *
     * NAME             => string; The column name
     * SIZE             => int|null; Length of index column (always null if index is unique)
     * POSITION         => int; Position in index
     *
     * @var array
     */
    protected $_indexes = [];
    /**
     * Foreign key descriptions for a table
     *
     * Is an associative array keyed by the uppercase foreign key name
     * The value of each array element is an associative array
     * with the following keys:
     *
     * FK_NAME          => string; The foreign key name
     * COLUMN_NAME      => string; The column name in table
     * REF_TABLE_NAME   => string; Reference table name
     * REF_COLUMN_NAME  => string; Reference table column name
     * ON_DELETE        => string; Integrity Actions, default NO ACTION
     * ON_UPDATE        => string; Integrity Actions, default NO ACTION
     *
     * Valid Integrity Actions:
     * CASCADE | SET NULL | NONE | RESTRICT | SET DEFAULT
     *
     * @var array
     */
    protected $_foreign_keys = [];
    /**
     * Additional table options
     *
     * @var array
     */
    protected $_options = ['type' => 'INNODB', 'charset' => 'utf8', 'collate' => 'utf8_general_ci'];
    /***
     * @var DtoTable|null
     */
    private ?Dto_Table $dto_table;
    /***
     * constructor
     *
     * @param DtoTable|null $DtoTable
     */
    public function __construct(?Dto_Table $dto_table = null)
    {
        $this->dto_table = $dto_table ?: Object_Manager::get_instance()->get(Dto_Table::class);
    }
    /**
     * Set table name
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name)
    {
        $this->_table_name = $name;
        if ($this->_table_comment === null) {
            $this->_table_comment = $name;
        }
        return $this;
    }
    /**
     * Set schema name
     *
     * @param string $name
     * @return $this
     */
    public function set_schema($name)
    {
        $this->_schema_name = $name;
        return $this;
    }
    /**
     * Set comment for table
     *
     * @param string $comment
     * @return $this
     */
    public function set_comment($comment)
    {
        $this->_table_comment = $comment;
        return $this;
    }
    /**
     * Retrieve name of table
     *
     * @return string
     * @throws \Zend_Db_Exception
     */
    public function get_name()
    {
        if ($this->_table_name === null) {
            throw new \Zend_Db_Exception('Table name is not defined');
        }
        return $this->_table_name;
    }
    /**
     * Get schema name
     *
     * @return string|null
     */
    public function get_schema()
    {
        return $this->_schema_name;
    }
    /**
     * Return comment for table
     *
     * @return string
     */
    public function get_comment()
    {
        return $this->_table_comment;
    }
    /**
     * Adds column to table.
     *
     * $options contains additional options for columns. Supported values are:
     * - 'unsigned', for number types only. Default: FALSE.
     * - 'precision', for numeric and decimal only. Default: taken from $size, if not set there then 0.
     * - 'scale', for numeric and decimal only. Default: taken from $size, if not set there then 10.
     * - 'default'. Default: not set.
     * - 'nullable'. Default: TRUE.
     * - 'primary', add column to primary index. Default: do not add.
     * - 'primary_position', only for column in primary index. Default: count of primary columns + 1.
     * - 'identity' or 'auto_increment'. Default: FALSE.
     *
     * @param string $name the column name
     * @param string $type the column data type
     * @param string|int|array $size the column length
     * @param array $options array of additional options
     * @param string $comment column description
     * @return $this
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function add_column($name, $type, $size = null, $options = [], $comment = null)
    {
        $position = count($this->_columns);
        $default = false;
        $nullable = true;
        $length = null;
        $scale = null;
        $precision = null;
        $unsigned = false;
        $primary = false;
        $primary_position = 0;
        $identity = false;
        // Prepare different properties
        switch ($type) {
            case self::TYPE_BOOLEAN:
                break;
            case self::TYPE_SMALLINT:
            case self::TYPE_INTEGER:
            case self::TYPE_BIGINT:
                if (!empty($options['unsigned'])) {
                    $unsigned = true;
                }
                break;
            case self::TYPE_FLOAT:
                if (!empty($options['unsigned'])) {
                    $unsigned = true;
                }
                break;
            case self::TYPE_DECIMAL:
            case self::TYPE_NUMERIC:
                $match = [];
                $scale = 0;
                $precision = 10;
                // parse size value
                if (is_array($size)) {
                    if (count($size) == 2) {
                        $size = array_values($size);
                        $precision = $size[0];
                        $scale = $size[1];
                    }
                } elseif ($size && preg_match('#^(\d+),(\d+)$#', $size, $match)) {
                    $precision = $match[1];
                    $scale = $match[2];
                }
                // check options
                if (isset($options['precision'])) {
                    $precision = $options['precision'];
                }
                if (isset($options['scale'])) {
                    $scale = $options['scale'];
                }
                if (!empty($options['unsigned'])) {
                    $unsigned = true;
                }
                break;
            case self::TYPE_DATE:
            case self::TYPE_DATETIME:
            case self::TYPE_TIMESTAMP:
                break;
            case self::TYPE_TEXT:
            case self::TYPE_BLOB:
            case self::TYPE_VARBINARY:
                $length = $size;
                break;
            default:
                throw new \Zend_Db_Exception('Invalid column data type "' . $type . '"');
        }
        if (array_key_exists('default', $options)) {
            $default = $options['default'];
        }
        if (array_key_exists('nullable', $options)) {
            $nullable = (bool) $options['nullable'];
        }
        if (!empty($options['primary'])) {
            $primary = true;
            if (isset($options['primary_position'])) {
                $primary_position = (int) $options['primary_position'];
            } else {
                $primary_position = 0;
                foreach ($this->_columns as $v) {
                    if ($v['PRIMARY']) {
                        $primary_position++;
                    }
                }
            }
        }
        if (!empty($options['identity']) || !empty($options['auto_increment'])) {
            $identity = true;
        }
        if ($comment === null) {
            $comment = ucfirst($name);
        }
        $upper_name = strtoupper($name);
        $this->_columns[$upper_name] = ['COLUMN_NAME' => $name, 'COLUMN_TYPE' => $type, 'COLUMN_POSITION' => $position, 'DATA_TYPE' => $type, 'DEFAULT' => $default, 'NULLABLE' => $nullable, 'LENGTH' => $length, 'SCALE' => $scale, 'PRECISION' => $precision, 'UNSIGNED' => $unsigned, 'PRIMARY' => $primary, 'PRIMARY_POSITION' => $primary_position, 'IDENTITY' => $identity, 'COMMENT' => $comment];
        return $this;
    }
    /**
     * Add Foreign Key to table
     *
     * @param string $fkName        the foreign key name
     * @param string $column        the foreign key column name
     * @param string $refTable      the reference table name
     * @param string $refColumn     the reference table column name
     * @param string $onDelete      the action on delete row
     * @return $this
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function add_foreign_key($fk_name, $column, $ref_table, $ref_column, $on_delete = null)
    {
        $upper_name = strtoupper($fk_name);
        // validate column name
        if (!isset($this->_columns[strtoupper($column)])) {
            throw new \Zend_Db_Exception('Undefined column "' . $column . '"');
        }
        switch ($on_delete) {
            case self::ACTION_CASCADE:
            case self::ACTION_RESTRICT:
            case self::ACTION_SET_DEFAULT:
            case self::ACTION_SET_NULL:
                break;
            default:
                $on_delete = self::ACTION_NO_ACTION;
        }
        $this->_foreign_keys[$upper_name] = ['FK_NAME' => $fk_name, 'COLUMN_NAME' => $column, 'REF_TABLE_NAME' => $ref_table, 'REF_COLUMN_NAME' => $ref_column, 'ON_DELETE' => $on_delete];
        return $this;
    }
    /**
     * Add index to table
     *
     * @param string $indexName the index name
     * @param array|string $fields array of columns or column string
     * @param array $options array of additional options
     * @return $this
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function add_index($index_name, $fields, $options = [])
    {
        $idx_type = Adapter_Interface::INDEX_TYPE_INDEX;
        $position = 0;
        $columns = [];
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        foreach ($fields as $column_data) {
            $column_size = null;
            $column_pos = $position;
            if (is_string($column_data)) {
                $column_name = $column_data;
            } elseif (is_array($column_data)) {
                if (!isset($column_data['name'])) {
                    throw new \Zend_Db_Exception('Invalid index column data');
                }
                $column_name = $column_data['name'];
                if (!empty($column_data['size'])) {
                    $column_size = (int) $column_data['size'];
                }
                if (!empty($column_data['position'])) {
                    $column_pos = (int) $column_data['position'];
                }
            } else {
                continue;
            }
            $columns[strtoupper($column_name)] = ['NAME' => $column_name, 'SIZE' => $column_size, 'POSITION' => $column_pos];
            $position++;
        }
        if (empty($columns)) {
            throw new \Zend_Db_Exception('Columns for index are not defined');
        }
        if (!empty($options['type'])) {
            $idx_type = $options['type'];
        }
        $this->_indexes[strtoupper($index_name)] = ['INDEX_NAME' => $index_name, 'COLUMNS' => $this->_normalize_index_column_position($columns), 'TYPE' => $idx_type];
        return $this;
    }
    /**
     * Retrieve array of table columns
     *
     * @param bool $normalized
     * @see $this->_columns
     * @return array
     */
    public function get_columns($normalized = true)
    {
        if ($normalized) {
            return $this->_normalize_column_position($this->_columns);
        }
        return $this->_columns;
    }
    /**
     * Set column, formatted according to DDL Table format, into columns structure
     *
     * @param array $column
     * @see $this->_columns
     * @return $this
     */
    public function set_column($column)
    {
        $upper_name = strtoupper($column['COLUMN_NAME']);
        $this->_columns[$upper_name] = $column;
        return $this;
    }
    /**
     * Retrieve array of table indexes
     *
     * @see $this->_indexes
     * @return array
     */
    public function get_indexes()
    {
        return $this->_indexes;
    }
    /**
     * Retrieve array of table foreign keys
     *
     * @see $this->_foreignKeys
     * @return array
     */
    public function get_foreign_keys()
    {
        return $this->_foreign_keys;
    }
    /**
     * Set table option
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function set_option($key, $value)
    {
        $this->_options[$key] = $value;
        return $this;
    }
    /**
     * Retrieve table option value by option name
     *
     * Return null if option does not exist
     *
     * @param string $key
     * @return null|string
     */
    public function get_option($key)
    {
        if (!isset($this->_options[$key])) {
            return null;
        }
        if (strtolower($key) == 'charset') {
            return $this->dto_table->get_default_charset();
        }
        if (strtolower($key) == 'collate') {
            return $this->dto_table->get_default_collation();
        }
        return $this->_options[$key];
    }
    /**
     * Retrieve array of table options
     *
     * @return array
     */
    public function get_options()
    {
        return $this->_options;
    }
    /**
     * Index column position comparison function
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sort_index_column_position($a, $b)
    {
        return $a['POSITION'] - $b['POSITION'];
    }
    /**
     * Table column position comparison function
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sort_column_position($a, $b)
    {
        return $a['COLUMN_POSITION'] - $b['COLUMN_POSITION'];
    }
    /**
     * Normalize positon of index columns array
     *
     * @param array $columns
     * @return array
     */
    protected function _normalize_index_column_position($columns)
    {
        uasort($columns, [$this, '_sortIndexColumnPosition']);
        $position = 0;
        foreach (array_keys($columns) as $column_id) {
            $columns[$column_id]['POSITION'] = $position;
            $position++;
        }
        return $columns;
    }
    /**
     * Normalize positon of table columns array
     *
     * @param array $columns
     * @return array
     */
    protected function _normalize_column_position($columns)
    {
        uasort($columns, [$this, '_sortColumnPosition']);
        $position = 0;
        foreach (array_keys($columns) as $column_id) {
            $columns[$column_id]['COLUMN_POSITION'] = $position;
            $position++;
        }
        return $columns;
    }
}