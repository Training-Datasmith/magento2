<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Adapter\Pdo;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Cache\Cache_Constants;
use Magento\Framework\Cache\Frontend_Interface;
use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\DB\Adapter\Connection_Exception;
use Magento\Framework\DB\Adapter\Deadlock_Exception;
use Magento\Framework\DB\Adapter\Duplicate_Exception;
use Magento\Framework\DB\Adapter\Lock_Wait_Exception;
use Magento\Framework\DB\Adapter\Table_Not_Found_Exception;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Expression_Converter;
use Magento\Framework\DB\Logger_Interface;
use Magento\Framework\DB\Profiler;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select_Factory;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\DB\Statement\Parameter;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer_Interface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Factories\Table as DtoFactoriesTable;
use Magento\Framework\Setup\Schema_Listener;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\String_Utils;
use Zend_Db_Adapter_Exception;
use Zend_Db_Statement_Exception;
// @codingStandardsIgnoreStart
/**
 * MySQL database adapter
 *
 * @api
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Mysql extends \Zend_Db_Adapter_Pdo_Mysql implements Adapter_Interface, Reset_After_Request_Interface
{
    // @codingStandardsIgnoreEnd
    public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';
    public const DDL_DESCRIBE = 1;
    public const DDL_CREATE = 2;
    public const DDL_INDEX = 3;
    public const DDL_FOREIGN_KEY = 4;
    private const DDL_EXISTS = 5;
    public const DDL_CACHE_PREFIX = 'DB_PDO_MYSQL_DDL';
    public const DDL_CACHE_TAG = 'DB_PDO_MYSQL_DDL';
    public const LENGTH_TABLE_NAME = 64;
    public const LENGTH_INDEX_NAME = 64;
    public const LENGTH_FOREIGN_NAME = 64;
    /**
     * MEMORY engine type for MySQL tables
     */
    public const ENGINE_MEMORY = 'MEMORY';
    /**
     * Maximum number of connection retries
     */
    public const MAX_CONNECTION_RETRIES = 10;
    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_default_stmt_class = \Magento\Framework\DB\Statement\Pdo\Mysql::class;
    /**
     * Current Transaction Level
     *
     * @var int
     */
    protected $_transaction_level = 0;
    /**
     * Whether transaction was rolled back or not
     *
     * @var bool
     */
    protected $_is_rolled_back = false;
    /**
     * Set attribute to connection flag
     *
     * @var bool
     */
    protected $_connection_flags_set = false;
    /**
     * Tables DDL cache
     *
     * @var array
     */
    protected $_ddl_cache = [];
    /**
     * SQL bind params. Used temporarily by regexp callback.
     *
     * @var array
     */
    protected $_bind_params = [];
    /**
     * Autoincrement for bind value. Used by regexp callback.
     *
     * @var int
     */
    protected $_bind_increment = 0;
    /**
     * Cache frontend adapter instance
     *
     * @var FrontendInterface
     */
    protected $_cache_adapter;
    /**
     * DDL cache allowing flag
     * @var bool
     */
    protected $_is_ddl_cache_allowed = true;
    /**
     * Save if mysql engine is 8 or not.
     *
     * @var bool
     */
    private $is_mysql8engine;
    /***
     * const for column type
     */
    private const COLUMN_TYPE = ['varchar', 'char', 'text', 'mediumtext', 'longtext'];
    /**
     * MySQL column - Table DDL type pairs
     *
     * @var array
     */
    protected $_ddl_column_types = [Table::TYPE_BOOLEAN => 'bool', Table::TYPE_SMALLINT => 'smallint', Table::TYPE_INTEGER => 'int', Table::TYPE_BIGINT => 'bigint', Table::TYPE_FLOAT => 'float', Table::TYPE_DECIMAL => 'decimal', Table::TYPE_NUMERIC => 'decimal', Table::TYPE_DATE => 'date', Table::TYPE_TIMESTAMP => 'timestamp', Table::TYPE_DATETIME => 'datetime', Table::TYPE_TEXT => 'text', Table::TYPE_BLOB => 'blob', Table::TYPE_VARBINARY => 'blob'];
    /**
     * All possible DDL statements
     * First 3 symbols for each statement
     *
     * @var string[]
     */
    protected $_ddl_routines = ['alt', 'cre', 'ren', 'dro', 'tru'];
    /**
     * Allowed interval units array
     *
     * @var array
     */
    protected $_interval_units = [self::INTERVAL_YEAR => 'YEAR', self::INTERVAL_MONTH => 'MONTH', self::INTERVAL_DAY => 'DAY', self::INTERVAL_HOUR => 'HOUR', self::INTERVAL_MINUTE => 'MINUTE', self::INTERVAL_SECOND => 'SECOND'];
    /**
     * Hook callback to modify queries. Mysql specific property, designed only for backwards compatibility.
     *
     * @var array|null
     */
    protected $_query_hook = null;
    /**
     * @var StringUtils
     */
    protected $string;
    /**
     * @var DateTime
     */
    protected $date_time;
    /**
     * @var SelectFactory
     * @since 100.1.0
     */
    protected $select_factory;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * Map that links database error code to corresponding Magento exception
     *
     * @var Zend_Db_Adapter_Exception[]
     */
    private $exception_map;
    /**
     * @var QueryGenerator
     */
    private $query_generator;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var SchemaListener
     */
    private $schema_listener;
    /**
     * Process id that the connection is associated with
     *
     * @var int|null
     */
    private ?int $pid = null;
    /**
     * Parent process's database connection
     *
     * @var array
     */
    private $parent_connections = [];
    /***
     * Get exact version of MySQL
     *
     * @var string
     */
    private $mysqlversion;
    /***
     * @var DtoFactoriesTable
     */
    private $column_config;
    /**
     * Constructor
     *
     * @param StringUtils $string
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param SelectFactory $selectFactory
     * @param array $config
     * @param SerializerInterface|null $serializer
     * @param DtoFactoriesTable|null $dtoFactoriesTable
     */
    public function __construct(String_Utils $string, DateTime $date_time, Logger_Interface $logger, Select_Factory $select_factory, array $config = [], ?Serializer_Interface $serializer = null, ?Dto_Factories_Table $dto_factories_table = null)
    {
        $this->pid = getmypid();
        $this->string = $string;
        $this->date_time = $date_time;
        $this->logger = $logger;
        $this->select_factory = $select_factory;
        $this->column_config = $dto_factories_table ?: Object_Manager::get_instance()->get(Dto_Factories_Table::class);
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Serializer_Interface::class);
        $this->exception_map = [
            // SQLSTATE[HY000]: General error: 2006 MySQL server has gone away
            2006 => Connection_Exception::class,
            // SQLSTATE[HY000]: General error: 2013 Lost connection to MySQL server during query
            2013 => Connection_Exception::class,
            // SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded
            1205 => Lock_Wait_Exception::class,
            // SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock
            1213 => Deadlock_Exception::class,
            // SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
            1062 => Duplicate_Exception::class,
            // SQLSTATE[42S02]: Base table or view not found: 1146
            1146 => Table_Not_Found_Exception::class,
        ];
        try {
            parent::__construct($config);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new \InvalidArgumentException($e->get_message(), $e->get_code(), $e);
        }
    }
    /**
     * @inheritdoc
     */
    public function _reset_state(): void
    {
        $this->_transaction_level = 0;
        $this->_is_rolled_back = false;
        $this->_connection_flags_set = false;
        $this->_ddl_cache = [];
        $this->_bind_params = [];
        $this->_bind_increment = 0;
        $this->_is_ddl_cache_allowed = true;
        $this->is_mysql8engine = null;
        $this->_query_hook = null;
        $this->avoid_reusing_parent_process_connection();
        $this->close_connection();
    }
    /**
     * Begin new DB transaction for connection
     *
     * @return $this
     * @throws \Exception
     */
    public function begin_transaction()
    {
        if ($this->_is_rolled_back) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new \Exception(Adapter_Interface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE);
        }
        if ($this->_transaction_level === 0) {
            $this->logger->start_timer();
            try {
                $this->perform_query(function () {
                    parent::begin_transaction();
                });
            } finally {
                $this->logger->log_stats(Logger_Interface::TYPE_TRANSACTION, 'BEGIN');
            }
        }
        ++$this->_transaction_level;
        return $this;
    }
    /**
     * Commit DB transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function commit()
    {
        if ($this->_transaction_level === 1 && !$this->_is_rolled_back) {
            $this->logger->start_timer();
            parent::commit();
            $this->logger->log_stats(Logger_Interface::TYPE_TRANSACTION, 'COMMIT');
        } elseif ($this->_transaction_level === 0) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new \Exception(Adapter_Interface::ERROR_ASYMMETRIC_COMMIT_MESSAGE);
        } elseif ($this->_is_rolled_back) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new \Exception(Adapter_Interface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE);
        }
        --$this->_transaction_level;
        return $this;
    }
    /**
     * Rollback DB transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function roll_back()
    {
        if ($this->_transaction_level === 1) {
            $this->logger->start_timer();
            parent::roll_back();
            $this->_is_rolled_back = false;
            $this->logger->log_stats(Logger_Interface::TYPE_TRANSACTION, 'ROLLBACK');
        } elseif ($this->_transaction_level === 0) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new \Exception(Adapter_Interface::ERROR_ASYMMETRIC_ROLLBACK_MESSAGE);
        } else {
            $this->_is_rolled_back = true;
        }
        --$this->_transaction_level;
        return $this;
    }
    /**
     * Get adapter transaction level state. Return 0 if all transactions are complete
     *
     * @return int
     */
    public function get_transaction_level()
    {
        return $this->_transaction_level;
    }
    /**
     * Convert date to DB format
     *
     * @param int|string|\DateTimeInterface $date
     * @return \Zend_Db_Expr
     */
    public function convert_date($date)
    {
        return $this->format_date($date, false);
    }
    /**
     * Convert date and time to DB format
     *
     * @param int|string|\DateTimeInterface $datetime
     * @return \Zend_Db_Expr
     */
    public function convert_date_time($datetime)
    {
        return $this->format_date($datetime, true);
    }
    /**
     * If the connection is associated to a different process id, then we need to not use it.
     *
     * @return void
     */
    private function avoid_reusing_parent_process_connection()
    {
        if (getmypid() != $this->pid) {
            // Note: we hide parent's connection into parentConnections so that the destructor isn't called on it.
            // Because if destructor is called, it causes parent's connection to die
            // We store in array, if parent is also hiding its parent's connection
            $this->parent_connections[] = $this->_connection;
            $this->_connection = null;
            $this->pid = getmypid();
            // Reset config host to avoid issue with multiple connections
            if (!empty($this->_config['port']) && strpos($this->_config['host'], ':') === false) {
                $this->_config['host'] = implode(':', [$this->_config['host'], $this->_config['port']]);
                unset($this->_config['port']);
            }
        }
    }
    /**
     * Creates a PDO object and connects to the database.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function _connect()
    {
        $this->avoid_reusing_parent_process_connection();
        if ($this->_connection) {
            return;
        }
        if (!extension_loaded('pdo_mysql')) {
            throw new Zend_Db_Adapter_Exception('pdo_mysql extension is not installed');
        }
        if (!isset($this->_config['host'])) {
            throw new Zend_Db_Adapter_Exception('No host configured to connect');
        }
        if (isset($this->_config['port'])) {
            throw new Zend_Db_Adapter_Exception('Port must be configured within host parameter (like localhost:3306');
        }
        unset($this->_config['port']);
        if (strpos($this->_config['host'], '/') !== false) {
            $this->_config['unix_socket'] = $this->_config['host'];
            unset($this->_config['host']);
        } elseif (strpos($this->_config['host'], ':') !== false) {
            list($this->_config['host'], $this->_config['port']) = explode(':', $this->_config['host']);
        }
        $multi_stmt_attr = $this->get_mysql_constant('ATTR_MULTI_STATEMENTS');
        if (!isset($this->_config['driver_options'][$multi_stmt_attr])) {
            $this->_config['driver_options'][$multi_stmt_attr] = false;
        }
        if (!isset($this->_config['driver_options'][\PDO::ATTR_STRINGIFY_FETCHES])) {
            $this->_config['driver_options'][\PDO::ATTR_STRINGIFY_FETCHES] = true;
        }
        $this->logger->start_timer();
        parent::_connect();
        $this->logger->log_stats(Logger_Interface::TYPE_CONNECT, '');
        /** @link http://bugs.mysql.com/bug.php?id=18551 */
        $this->_connection->query("SET SQL_MODE=''");
        // As we use default value CURRENT_TIMESTAMP for TIMESTAMP type columns we need to set GMT timezone
        $this->_connection->query("SET time_zone = '+00:00'");
        if (isset($this->_config['initStatements'])) {
            $statements = $this->_split_multi_query($this->_config['initStatements']);
            foreach ($statements as $statement) {
                $this->_query($statement);
            }
        }
        if (!$this->_connection_flags_set) {
            $this->_connection->set_attribute(\PDO::ATTR_EMULATE_PREPARES, true);
            $buffered_query_attr = $this->get_mysql_constant('ATTR_USE_BUFFERED_QUERY');
            if (isset($this->_config['use_buffered_query']) && $this->_config['use_buffered_query'] === false) {
                $this->_connection->set_attribute($buffered_query_attr, false);
            } else {
                $this->_connection->set_attribute($buffered_query_attr, true);
            }
            $this->_connection_flags_set = true;
        }
    }
    /**
     * Get MySQL-specific PDO constant for backward compatibility
     *
     * PHP 8.5 deprecated PDO::MYSQL_ATTR_* in favor of Pdo\Mysql::ATTR_*
     * This method provides compatibility across PHP 8.2-8.5
     *
     * @param string $constantName Constant name without prefix (e.g., 'ATTR_MULTI_STATEMENTS')
     * @return int
     */
    private function get_mysql_constant(string $constant_name): int
    {
        if (version_compare(PHP_VERSION, '8.4') < 0) {
            return constant('PDO::MYSQL_' . $constant_name);
        } else {
            return constant('Pdo\Mysql::' . $constant_name);
        }
    }
    /**
     * Create new database connection
     *
     * @return \PDO
     */
    private function create_connection()
    {
        $connection = new \PDO($this->_dsn(), $this->_config['username'], $this->_config['password'], $this->_config['driver_options']);
        return $connection;
    }
    /**
     * Run RAW Query
     *
     * @param string $sql
     * @return \Zend_Db_Statement_Interface
     * @throws \PDOException
     */
    public function raw_query($sql)
    {
        try {
            $result = $this->query($sql);
        } catch (Zend_Db_Statement_Exception $e) {
            // Convert to \PDOException to maintain backwards compatibility with usage of MySQL adapter
            $e = $e->get_previous();
            if (!$e instanceof \PDOException) {
                $e = new \PDOException($e->get_message(), $e->get_code());
            }
            throw $e;
        }
        return $result;
    }
    /**
     * Run RAW query and Fetch First row
     *
     * @param string $sql
     * @param string|int $field
     * @return mixed|null
     */
    public function raw_fetch_row($sql, $field = null)
    {
        $result = $this->raw_query($sql);
        if (!$result) {
            return false;
        }
        $row = $result->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        if (empty($field)) {
            return $row;
        } else {
            return $row[$field] ?? false;
        }
    }
    /**
     * Check transaction level in case of DDL query
     *
     * @param string|\Magento\Framework\DB\Select $sql
     * @return void
     * @throws Zend_Db_Adapter_Exception
     */
    protected function _check_ddl_transaction($sql)
    {
        if ($this->get_transaction_level() > 0) {
            $sql = $sql !== null ? ltrim(preg_replace('/\s+/', ' ', $sql)) : '';
            $sql_message = explode(' ', $sql, 3);
            $start_sql = strtolower(substr($sql_message[0], 0, 3));
            if (in_array($start_sql, $this->_ddl_routines) && strcasecmp($sql_message[1], 'temporary') !== 0) {
                throw new Connection_Exception(Adapter_Interface::ERROR_DDL_MESSAGE, E_USER_ERROR);
            }
        }
    }
    /**
     * Special handling for PDO query().
     *
     * All bind parameter names must begin with ':'.
     *
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Pdo|void
     * @throws Zend_Db_Adapter_Exception To re-throw \PDOException.
     * @throws Zend_Db_Statement_Exception
     */
    protected function _query($sql, $bind = [])
    {
        $result = null;
        try {
            $this->_check_ddl_transaction($sql);
            $this->_prepare_query($sql, $bind);
            $this->logger->start_timer();
            $result = $this->perform_query(fn() => parent::query($sql, $bind));
        } finally {
            $this->logger->log_stats(Logger_Interface::TYPE_QUERY, $sql, $bind, $result);
        }
        return $result;
    }
    /**
     * Execute query and reconnect if needed.
     *
     * @param callable $queryExecutor
     * @return \Zend_Db_Statement_Pdo|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function perform_query(callable $query_executor)
    {
        $connection_errors = [
            2006,
            // SQLSTATE[HY000]: General error: 2006 MySQL server has gone away
            2013,
            // SQLSTATE[HY000]: General error: 2013 Lost connection to MySQL server during query
            4031,
        ];
        $tries_count = 0;
        do {
            $retry = false;
            try {
                return $query_executor();
            } catch (\Exception $e) {
                // Finalize broken query
                $profiler = $this->get_profiler();
                if ($profiler instanceof Profiler) {
                    $profiler->query_end_last();
                }
                $pdo_exception = null;
                if ($e instanceof \PDOException) {
                    $pdo_exception = $e;
                } elseif ($e instanceof Zend_Db_Statement_Exception && $e->get_previous() instanceof \PDOException) {
                    $pdo_exception = $e->get_previous();
                }
                // Check to reconnect
                if ($pdo_exception && $tries_count < self::MAX_CONNECTION_RETRIES && in_array($pdo_exception->error_info[1], $connection_errors)) {
                    $retry = true;
                    $tries_count++;
                    $this->close_connection();
                    $this->_connect();
                }
                if (!$retry) {
                    $this->logger->critical($e);
                    // rethrow custom exception if needed
                    if ($pdo_exception && isset($this->exception_map[$pdo_exception->error_info[1]])) {
                        $custom_exception_class = $this->exception_map[$pdo_exception->error_info[1]];
                        /** @var Zend_Db_Adapter_Exception $customException */
                        $custom_exception = new $custom_exception_class($e->get_message(), $pdo_exception->error_info[1], $e);
                        throw $custom_exception;
                    }
                    throw $e;
                }
            }
        } while ($retry);
    }
    /**
     * Special handling for PDO query().
     *
     * All bind parameter names must begin with ':'.
     *
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Pdo|void
     * @throws Zend_Db_Adapter_Exception To re-throw \PDOException.
     * @throws LocalizedException In case multiple queries are attempted at once, to protect from SQL injection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function query($sql, $bind = [])
    {
        if ($sql !== null && strpos(rtrim($sql, " \t\n\r\x00;"), ';') !== false && count($this->_split_multi_query($sql)) > 1) {
            throw new \Magento\Framework\Exception\Localized_Exception(new Phrase("Multiple queries can't be executed. Run a single query and try again."));
        }
        return $this->_query($sql, $bind);
    }
    /**
     * Allows multiple queries
     *
     * Allows multiple queries -- to safeguard against SQL injection, USE CAUTION and verify that input
     * cannot be tampered with.
     * Special handling for PDO query().
     * All bind parameter names must begin with ':'.
     *
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Pdo|void
     * @throws Zend_Db_Adapter_Exception To re-throw \PDOException.
     * @throws LocalizedException In case multiple queries are attempted at once, to protect from SQL injection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @deprecated 101.0.0
     * @see _query
     */
    public function multi_query($sql, $bind = [])
    {
        return $this->_query($sql, $bind);
    }
    /**
     * Prepares SQL query by moving to bind all special parameters that can be confused with bind placeholders
     * (e.g. "foo:bar"). And also changes named bind to positional one, because underlying library has problems
     * with named binds.
     *
     * @param \Magento\Framework\DB\Select|string $sql
     * @param mixed $bind
     * @return $this
     */
    protected function _prepare_query(&$sql, &$bind = [])
    {
        $sql = (string) $sql;
        if (!is_array($bind)) {
            $bind = [$bind];
        }
        // Mixed bind is not supported - so remember whether it is named bind, to normalize later if required
        if ($bind) {
            foreach ($bind as $k => $v) {
                if (!is_int($k)) {
                    if ($k[0] != ':') {
                        $bind[":{$k}"] = $v;
                        unset($bind[$k]);
                    }
                }
            }
        }
        // Special query hook
        if ($this->_query_hook) {
            $object = $this->_query_hook['object'];
            $method = $this->_query_hook['method'];
            $object->{$method}($sql, $bind);
        }
        return $this;
    }
    /**
     * Callback function for preparation of query and bind by regexp.
     * Checks query parameters for special symbols and moves such parameters to bind array as named ones.
     * This method writes to $_bindParams, where query bind parameters are kept.
     * This method requires further normalizing, if bind array is positional.
     *
     * @param string[] $matches
     * @return string
     */
    public function proccess_bind_callback($matches)
    {
        if (isset($matches[6]) && (strpos($matches[6], "'") !== false || strpos($matches[6], ':') !== false || strpos($matches[6], '?') !== false)) {
            $bind_name = ':_mage_bind_var_' . ++$this->_bind_increment;
            $this->_bind_params[$bind_name] = $this->_un_quote($matches[6]);
            return ' ' . $bind_name;
        }
        return $matches[0];
    }
    /**
     * Unquote raw string (use for auto-bind)
     *
     * @param string $string
     * @return string
     */
    protected function _un_quote($string)
    {
        $translate = ['\000' => "\x00", '\n' => "\n", '\r' => "\r", '\\\\' => '\\', "\\'" => "'", '\"' => '"', '\032' => "\x1a"];
        return strtr($string, $translate);
    }
    /**
     * Normalizes mixed positional-named bind to positional bind, and replaces named placeholders in query to
     * '?' placeholders.
     *
     * @param string $sql
     * @param array $bind
     * @return $this
     */
    protected function _convert_mixed_bind(&$sql, &$bind)
    {
        $positions = [];
        $offset = 0;
        $sql = (string) $sql;
        // get positions
        while (true) {
            $pos = strpos($sql, '?', $offset);
            if ($pos !== false) {
                $positions[] = $pos;
                $offset = ++$pos;
            } else {
                break;
            }
        }
        $bind_result = [];
        $map = [];
        foreach ($bind as $k => $v) {
            // positional
            if (is_int($k)) {
                if (!isset($positions[$k])) {
                    continue;
                }
                $bind_result[$positions[$k]] = $v;
            } else {
                $offset = 0;
                while (true) {
                    $pos = strpos($sql, $k, $offset);
                    if ($pos === false) {
                        break;
                    } else {
                        $offset = $pos + strlen($k);
                        $bind_result[$pos] = $v;
                    }
                }
                $map[$k] = '?';
            }
        }
        ksort($bind_result);
        $bind = array_values($bind_result);
        $sql = strtr($sql, $map);
        return $this;
    }
    /**
     * Sets (removes) query hook.
     *
     * $hook must be either array with 'object' and 'method' entries, or null to remove hook.
     * Previous hook is returned.
     *
     * @param array $hook
     * @return array|null
     */
    public function set_query_hook($hook)
    {
        $prev = $this->_query_hook;
        $this->_query_hook = $hook;
        return $prev;
    }
    /**
     * Split multi statement query
     *
     * @param string $sql
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @deprecated 100.1.2
     * @see MAGETWO-60073
     */
    protected function _split_multi_query($sql)
    {
        $parts = preg_split('#(;|\'|"|\\\\|//|--|\n|/\*|\*/)#', $sql, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $q = false;
        $c = false;
        $stmts = [];
        $s = '';
        foreach ($parts as $i => $part) {
            // strings
            if (($part === "'" || $part === '"') && ($i === 0 || $parts[$i - 1] !== '\\')) {
                if ($q === false) {
                    $q = $part;
                } elseif ($q === $part) {
                    $q = false;
                }
            }
            // single line comments
            if (($part === '//' || $part === '--') && ($i === 0 || $parts[$i - 1] === "\n")) {
                $c = $part;
            } elseif ($part === "\n" && ($c === '//' || $c === '--')) {
                $c = false;
            }
            // multi line comments
            if ($part === '/*' && $c === false) {
                $c = '/*';
            } elseif ($part === '*/' && $c === '/*') {
                $c = false;
            }
            // statements
            if ($part === ';' && $q === false && $c === false) {
                if (trim($s) !== '') {
                    $stmts[] = trim($s);
                    $s = '';
                }
            } else {
                $s .= $part;
            }
        }
        if (trim($s) !== '') {
            $stmts[] = trim($s);
        }
        return $stmts;
    }
    /**
     * Drop the Foreign Key from table
     *
     * @param string $tableName
     * @param string $fkName
     * @param string $schemaName
     * @return $this
     */
    public function drop_foreign_key($table_name, $fk_name, $schema_name = null)
    {
        $foreign_keys = $this->get_foreign_keys($table_name, $schema_name);
        $fk_name = $fk_name !== null ? strtoupper($fk_name) : '';
        if (substr($fk_name, 0, 3) == 'FK_') {
            $fk_name = substr($fk_name, 3);
        }
        foreach ([$fk_name, 'FK_' . $fk_name] as $key) {
            if (isset($foreign_keys[$key])) {
                $sql = sprintf('ALTER TABLE %s DROP FOREIGN KEY %s', $this->quote_identifier($this->_get_table_name($table_name, $schema_name)), $this->quote_identifier($foreign_keys[$key]['FK_NAME']));
                $this->reset_ddl_cache($table_name, $schema_name);
                $this->raw_query($sql);
                $this->get_schema_listener()->drop_foreign_key($table_name, $fk_name);
            }
        }
        return $this;
    }
    /**
     * Prepare table before add constraint foreign key
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $refTableName
     * @param string $refColumnName
     * @param string $onDelete
     * @return $this
     */
    public function purge_orphan_records($table_name, $column_name, $ref_table_name, $ref_column_name, $on_delete = Adapter_Interface::FK_ACTION_CASCADE)
    {
        $on_delete = strtoupper($on_delete);
        if ($on_delete == Adapter_Interface::FK_ACTION_CASCADE || $on_delete == Adapter_Interface::FK_ACTION_RESTRICT) {
            $sql = sprintf('DELETE p.* FROM %s AS p LEFT JOIN %s AS r ON p.%s = r.%s WHERE r.%s IS NULL', $this->quote_identifier($table_name), $this->quote_identifier($ref_table_name), $this->quote_identifier($column_name), $this->quote_identifier($ref_column_name), $this->quote_identifier($ref_column_name));
            $this->raw_query($sql);
        } elseif ($on_delete == Adapter_Interface::FK_ACTION_SET_NULL) {
            $sql = sprintf('UPDATE %s AS p LEFT JOIN %s AS r ON p.%s = r.%s SET p.%s = NULL WHERE r.%s IS NULL', $this->quote_identifier($table_name), $this->quote_identifier($ref_table_name), $this->quote_identifier($column_name), $this->quote_identifier($ref_column_name), $this->quote_identifier($column_name), $this->quote_identifier($ref_column_name));
            $this->raw_query($sql);
        }
        return $this;
    }
    /**
     * Check does table column exist
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return bool
     */
    public function table_column_exists($table_name, $column_name, $schema_name = null)
    {
        $describe = $this->describe_table($table_name, $schema_name);
        foreach ($describe as $column) {
            if ($column['COLUMN_NAME'] == $column_name) {
                return true;
            }
        }
        return false;
    }
    /**
     * Adds new column to table.
     *
     * Generally $defintion must be array with column data to keep this call cross-DB compatible.
     * Using string as $definition is allowed only for concrete DB adapter.
     * Adds primary key if needed
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition string specific or universal array DB Server definition
     * @param string $schemaName
     * @return true|\Zend_Db_Statement_Pdo
     * @throws \Zend_Db_Exception
     */
    public function add_column($table_name, $column_name, $definition, $schema_name = null)
    {
        $this->get_schema_listener()->add_column($table_name, $column_name, $definition);
        if ($this->table_column_exists($table_name, $column_name, $schema_name)) {
            return true;
        }
        $primary_key = '';
        if (is_array($definition)) {
            $definition = array_change_key_case($definition, CASE_UPPER);
            if (empty($definition['COMMENT'])) {
                throw new \Zend_Db_Exception('Impossible to create a column without comment.');
            }
            if (!empty($definition['PRIMARY'])) {
                $primary_key = sprintf(', ADD PRIMARY KEY (%s)', $this->quote_identifier($column_name));
            }
            $definition = $this->_get_column_definition($definition);
        }
        $sql = sprintf('ALTER TABLE %s ADD COLUMN %s %s %s', $this->quote_identifier($this->_get_table_name($table_name, $schema_name)), $this->quote_identifier($column_name), $definition, $primary_key);
        $result = $this->raw_query($sql);
        $this->reset_ddl_cache($table_name, $schema_name);
        return $result;
    }
    /**
     * Delete table column
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return true|\Zend_Db_Statement_Pdo
     */
    public function drop_column($table_name, $column_name, $schema_name = null)
    {
        if (!$this->table_column_exists($table_name, $column_name, $schema_name)) {
            return true;
        }
        $this->get_schema_listener()->drop_column($table_name, $column_name);
        $alter_drop = [];
        $foreign_keys = $this->get_foreign_keys($table_name, $schema_name);
        foreach ($foreign_keys as $fk_prop) {
            if ($fk_prop['COLUMN_NAME'] == $column_name) {
                $this->get_schema_listener()->drop_foreign_key($table_name, $fk_prop['FK_NAME']);
                $alter_drop[] = 'DROP FOREIGN KEY ' . $this->quote_identifier($fk_prop['FK_NAME']);
            }
        }
        /* drop index that after column removal would coincide with the existing index by indexed columns */
        foreach ($this->get_index_list($table_name, $schema_name) as $idx_data) {
            $idx_columns = $idx_data['COLUMNS_LIST'];
            $idx_column_key = array_search($column_name, $idx_columns);
            if ($idx_column_key !== false) {
                unset($idx_columns[$idx_column_key]);
                if (empty($idx_columns)) {
                    $this->get_schema_listener()->drop_index($table_name, $idx_data['KEY_NAME'], 'index');
                }
                if ($idx_columns && $this->_get_index_by_columns($table_name, $idx_columns, $schema_name)) {
                    $this->drop_index($table_name, $idx_data['KEY_NAME'], $schema_name);
                }
            }
        }
        $alter_drop[] = 'DROP COLUMN ' . $this->quote_identifier($column_name);
        $sql = sprintf('ALTER TABLE %s %s', $this->quote_identifier($this->_get_table_name($table_name, $schema_name)), implode(', ', $alter_drop));
        $result = $this->raw_query($sql);
        $this->reset_ddl_cache($table_name, $schema_name);
        return $result;
    }
    /**
     * Retrieve index information by indexed columns or return NULL, if there is no index for a column list
     *
     * @param string $tableName
     * @param array $columns
     * @param string|null $schemaName
     * @return array|null
     */
    protected function _get_index_by_columns($table_name, array $columns, $schema_name)
    {
        foreach ($this->get_index_list($table_name, $schema_name) as $idx_data) {
            if ($idx_data['COLUMNS_LIST'] === $columns) {
                return $idx_data;
            }
        }
        return null;
    }
    /**
     * Change the column name and definition
     *
     * For change definition of column - use modifyColumn
     *
     * @param string $tableName
     * @param string $oldColumnName
     * @param string $newColumnName
     * @param array $definition
     * @param boolean $flushData flush table statistic
     * @param string $schemaName
     * @return \Zend_Db_Statement_Pdo
     * @throws \Zend_Db_Exception
     */
    public function change_column($table_name, $old_column_name, $new_column_name, $definition, $flush_data = false, $schema_name = null)
    {
        $this->get_schema_listener()->change_column($table_name, $old_column_name, $new_column_name, $definition);
        if (!$this->table_column_exists($table_name, $old_column_name, $schema_name)) {
            throw new \Zend_Db_Exception(sprintf('Column "%s" does not exist in table "%s".', $old_column_name, $table_name));
        }
        if (is_array($definition)) {
            $definition = $this->_get_column_definition($definition);
        }
        $sql = sprintf('ALTER TABLE %s CHANGE COLUMN %s %s %s', $this->quote_identifier($table_name), $this->quote_identifier($old_column_name), $this->quote_identifier($new_column_name), $definition);
        $result = $this->raw_query($sql);
        if ($flush_data) {
            $this->show_table_status($table_name, $schema_name);
        }
        $this->reset_ddl_cache($table_name, $schema_name);
        return $result;
    }
    /**
     * Modify the column definition
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function modify_column($table_name, $column_name, $definition, $flush_data = false, $schema_name = null)
    {
        $this->get_schema_listener()->modify_column($table_name, $column_name, $definition);
        if (!$this->table_column_exists($table_name, $column_name, $schema_name)) {
            throw new \Zend_Db_Exception(sprintf('Column "%s" does not exist in table "%s".', $column_name, $table_name));
        }
        if (is_array($definition)) {
            $definition = $this->_get_column_definition($definition);
        }
        // Set default collation to utf8mb4 for MySQL
        if (!empty($definition)) {
            $type = explode(' ', trim($definition));
            $definition = $this->set_default_charset_and_collation($type[0], $definition, 1);
        }
        $sql = sprintf('ALTER TABLE %s MODIFY COLUMN %s %s', $this->quote_identifier($table_name), $this->quote_identifier($column_name), $definition);
        $this->raw_query($sql);
        if ($flush_data) {
            $this->show_table_status($table_name, $schema_name);
        }
        $this->reset_ddl_cache($table_name, $schema_name);
        return $this;
    }
    /**
     * Show table status
     *
     * @param string $tableName
     * @param string $schemaName
     * @return mixed
     * @throws LocalizedException
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function show_table_status($table_name, $schema_name = null)
    {
        $from_db_name = null;
        if ($schema_name !== null) {
            $from_db_name = ' FROM ' . $this->quote_identifier($schema_name);
        }
        $query = sprintf('SHOW TABLE STATUS%s LIKE %s', $from_db_name, $this->quote($table_name));
        //checks which slq engine used
        if (!$this->is_mysql8engine_used()) {
            //if it's not MySQl-8 we just fetch results
            return $this->raw_fetch_row($query);
        }
        // Run show table status query in different connection because DDL queries do it in transaction,
        // and we don't have actual table statistic in this case
        $connection = $this->_transaction_level ? $this->create_connection() : $this;
        $connection->query(sprintf('ANALYZE TABLE %s', $this->quote_identifier($table_name)));
        return $connection->query($query)->fetch(\PDO::FETCH_ASSOC);
    }
    /**
     * Checks if the engine is mysql 8
     *
     * @return bool
     */
    private function is_mysql8engine_used(): bool
    {
        if (!$this->is_mysql8engine) {
            $version = $this->fetch_pairs("SHOW variables LIKE 'version'")['version'] ?? '';
            $this->is_mysql8engine = (bool) preg_match('/^(8\.)/', $version);
        }
        return $this->is_mysql8engine;
    }
    /**
     * Retrieve Create Table SQL
     *
     * @param string $tableName
     * @param string $schemaName
     * @return string
     */
    public function get_create_table($table_name, $schema_name = null)
    {
        $cache_key = $this->_get_table_name($table_name, $schema_name);
        $ddl = $this->load_ddl_cache($cache_key, self::DDL_CREATE);
        if ($ddl === false) {
            $sql = 'SHOW CREATE TABLE ' . $this->quote_identifier($this->_get_table_name($table_name, $schema_name));
            $ddl = $this->raw_fetch_row($sql, 'Create Table');
            $this->save_ddl_cache($cache_key, self::DDL_CREATE, $ddl);
        }
        return $ddl;
    }
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
     *
     * @param string $tableName
     * @param string $schemaName
     * @return array
     */
    public function get_foreign_keys($table_name, $schema_name = null)
    {
        $cache_key = $this->_get_table_name($table_name, $schema_name);
        $ddl = $this->load_ddl_cache($cache_key, self::DDL_FOREIGN_KEY);
        if ($ddl === false) {
            $ddl = [];
            $create_sql = $this->get_create_table($table_name, $schema_name);
            // collect CONSTRAINT
            $reg_exp = '#,\s+CONSTRAINT `([^`]*)` FOREIGN KEY ?\(`([^`]*)`\) ' . 'REFERENCES (`([^`]*)`\.)?`([^`]*)` \(`([^`]*)`\)' . '( ON DELETE (RESTRICT|CASCADE|SET NULL|NO ACTION))?' . '( ON UPDATE (RESTRICT|CASCADE|SET NULL|NO ACTION))?#';
            $matches = [];
            preg_match_all($reg_exp, $create_sql, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $ddl[strtoupper($match[1])] = ['FK_NAME' => $match[1], 'SCHEMA_NAME' => $schema_name, 'TABLE_NAME' => $table_name, 'COLUMN_NAME' => $match[2], 'REF_SHEMA_NAME' => isset($match[4]) ? $match[4] : $schema_name, 'REF_TABLE_NAME' => $match[5], 'REF_COLUMN_NAME' => $match[6], 'ON_DELETE' => isset($match[7]) ? $match[8] : ''];
            }
            $this->save_ddl_cache($cache_key, self::DDL_FOREIGN_KEY, $ddl);
        }
        return $ddl;
    }
    /**
     * Retrieve the foreign keys tree for all tables
     *
     * @return array
     */
    public function get_foreign_keys_tree()
    {
        $tree = [];
        foreach ($this->list_tables() as $table) {
            foreach ($this->get_foreign_keys($table) as $key) {
                $tree[$table][$key['COLUMN_NAME']] = $key;
            }
        }
        return $tree;
    }
    /**
     * Modify tables, used for upgrade process
     *
     * Change columns definitions, reset foreign keys, change tables comments and engines.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * columns => array; list of columns definitions
     * comment => string; table comment
     * engine  => string; table engine
     *
     * @param array $tables
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function modify_tables($tables)
    {
        $foreign_keys = $this->get_foreign_keys_tree();
        foreach ($tables as $table => $table_data) {
            if (!$this->is_table_exists($table)) {
                continue;
            }
            foreach ($table_data['columns'] as $column => $column_definition) {
                if (!$this->table_column_exists($table, $column)) {
                    continue;
                }
                $dropped_keys = [];
                foreach ($foreign_keys as $key_table => $columns) {
                    foreach ($columns as $column_name => $key_options) {
                        if ($table == $key_options['REF_TABLE_NAME'] && $column == $key_options['REF_COLUMN_NAME']) {
                            $this->drop_foreign_key($key_table, $key_options['FK_NAME']);
                            $dropped_keys[] = $key_options;
                        }
                    }
                }
                $this->modify_column($table, $column, $column_definition);
                foreach ($dropped_keys as $options) {
                    unset($column_definition['identity'], $column_definition['primary'], $column_definition['comment']);
                    $on_delete = $options['ON_DELETE'];
                    if ($on_delete == Adapter_Interface::FK_ACTION_SET_NULL) {
                        $column_definition['nullable'] = true;
                    }
                    $this->modify_column($options['TABLE_NAME'], $options['COLUMN_NAME'], $column_definition);
                    $this->add_foreign_key($options['FK_NAME'], $options['TABLE_NAME'], $options['COLUMN_NAME'], $options['REF_TABLE_NAME'], $options['REF_COLUMN_NAME'], $on_delete ? $on_delete : Adapter_Interface::FK_ACTION_NO_ACTION);
                }
            }
            if (!empty($table_data['comment'])) {
                $this->change_table_comment($table, $table_data['comment']);
            }
            if (!empty($table_data['engine'])) {
                $this->change_table_engine($table, $table_data['engine']);
            }
        }
        return $this;
    }
    /**
     * Retrieve table index information
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
     * @return array|string|int
     */
    public function get_index_list($table_name, $schema_name = null)
    {
        $cache_key = $this->_get_table_name($table_name, $schema_name);
        $ddl = $this->load_ddl_cache($cache_key, self::DDL_INDEX);
        if ($ddl === false) {
            $ddl = [];
            $sql = sprintf('SHOW INDEX FROM %s', $this->quote_identifier($this->_get_table_name($table_name, $schema_name)));
            foreach ($this->fetch_all($sql) as $row) {
                $field_key_name = 'Key_name';
                $field_non_unique = 'Non_unique';
                $field_column = 'Column_name';
                $field_index_type = 'Index_type';
                if (strtolower($row[$field_key_name] ?? '') == Adapter_Interface::INDEX_TYPE_PRIMARY) {
                    $index_type = Adapter_Interface::INDEX_TYPE_PRIMARY;
                } elseif ($row[$field_non_unique] == 0) {
                    $index_type = Adapter_Interface::INDEX_TYPE_UNIQUE;
                } elseif (strtolower($row[$field_index_type] ?? '') == Adapter_Interface::INDEX_TYPE_FULLTEXT) {
                    $index_type = Adapter_Interface::INDEX_TYPE_FULLTEXT;
                } else {
                    $index_type = Adapter_Interface::INDEX_TYPE_INDEX;
                }
                $upper_key_name = strtoupper($row[$field_key_name]);
                if (isset($ddl[$upper_key_name])) {
                    $ddl[$upper_key_name]['fields'][] = $row[$field_column];
                    // for compatible
                    $ddl[$upper_key_name]['COLUMNS_LIST'][] = $row[$field_column];
                } else {
                    $ddl[$upper_key_name] = [
                        'SCHEMA_NAME' => $schema_name,
                        'TABLE_NAME' => $table_name,
                        'KEY_NAME' => $row[$field_key_name],
                        'COLUMNS_LIST' => [$row[$field_column]],
                        'INDEX_TYPE' => $index_type,
                        'INDEX_METHOD' => $row[$field_index_type],
                        'type' => strtolower($index_type),
                        // for compatibility
                        'fields' => [$row[$field_column]],
                    ];
                }
            }
            $this->save_ddl_cache($cache_key, self::DDL_INDEX, $ddl);
        }
        return $ddl;
    }
    /**
     * Remove duplicate entry for create key
     *
     * @param string $table
     * @param array $fields
     * @param string[] $ids
     * @return $this
     */
    protected function _remove_duplicate_entry($table, $fields, $ids)
    {
        $where = [];
        $i = 0;
        foreach ($fields as $field) {
            $where[] = $this->quote_into($field . '=?', $ids[$i++]);
        }
        if (!$where) {
            return $this;
        }
        $where_cond = implode(' AND ', $where);
        $sql = sprintf('SELECT COUNT(*) as `cnt` FROM `%s` WHERE %s', $table, $where_cond);
        $cnt = $this->raw_fetch_row($sql, 'cnt');
        if ($cnt > 1) {
            $sql = sprintf('DELETE FROM `%s` WHERE %s LIMIT %d', $table, $where_cond, $cnt - 1);
            $this->raw_query($sql);
        }
        return $this;
    }
    /**
     * Creates and returns a new \Magento\Framework\DB\Select object for this adapter.
     *
     * @return Select
     */
    public function select()
    {
        return $this->select_factory->create($this);
    }
    /**
     * Quotes a value and places into a piece of text at a placeholder.
     *
     * Method rewrited for handle empty arrays in value param
     *
     * @param string $text The text with a placeholder.
     * @param array|null|int|string|float|Expression|Select|\DateTimeInterface $value The value to quote.
     * @param int|string|null $type OPTIONAL SQL datatype of the given value e.g. Zend_Db::FLOAT_TYPE or "INT"
     * @param integer $count OPTIONAL count of placeholders to replace
     * @return string An SQL-safe quoted value placed into the original text.
     */
    public function quote_into($text, $value, $type = null, $count = null)
    {
        if (is_array($value) && empty($value)) {
            $value = new \Zend_Db_Expr('NULL');
        }
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }
        return parent::quote_into($text, $value, $type, $count);
    }
    /**
     * Retrieve ddl cache name
     *
     * @param string $tableName
     * @param string $schemaName
     * @return string
     */
    protected function _get_table_name($table_name, $schema_name = null)
    {
        return ($schema_name ? $schema_name . '.' : '') . $table_name;
    }
    /**
     * Retrieve Id for cache
     *
     * @param string $tableKey
     * @param int $ddlType
     * @return string
     */
    protected function _get_cache_id($table_key, $ddl_type)
    {
        return sprintf('%s_%s_%s', self::DDL_CACHE_PREFIX, $table_key, $ddl_type);
    }
    /**
     * Load DDL data from cache
     *
     * Return false if cache does not exists
     *
     * @param string $tableCacheKey the table cache key
     * @param int $ddlType the DDL constant
     * @return string|array|int|false
     */
    public function load_ddl_cache($table_cache_key, $ddl_type)
    {
        if (!$this->_is_ddl_cache_allowed) {
            return false;
        }
        if (isset($this->_ddl_cache[$ddl_type][$table_cache_key])) {
            return $this->_ddl_cache[$ddl_type][$table_cache_key];
        }
        if ($this->_cache_adapter) {
            $cache_id = $this->_get_cache_id($table_cache_key, $ddl_type);
            $data = $this->_cache_adapter->load($cache_id);
            if ($data !== false) {
                $data = $this->serializer->unserialize($data);
                $this->_ddl_cache[$ddl_type][$table_cache_key] = $data;
            }
            return $data;
        }
        return false;
    }
    /**
     * Save DDL data into cache
     *
     * @param string $tableCacheKey
     * @param int $ddlType
     * @param array $data
     * @return $this
     */
    public function save_ddl_cache($table_cache_key, $ddl_type, $data)
    {
        if (!$this->_is_ddl_cache_allowed) {
            return $this;
        }
        $this->_ddl_cache[$ddl_type][$table_cache_key] = $data;
        if ($this->_cache_adapter) {
            $cache_id = $this->_get_cache_id($table_cache_key, $ddl_type);
            $data = $this->serializer->serialize($data);
            $this->_cache_adapter->save($data, $cache_id, [self::DDL_CACHE_TAG]);
        }
        return $this;
    }
    /**
     * Reset cached DDL data from cache
     *
     * If table name is null - reset all cached DDL data
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return $this
     */
    public function reset_ddl_cache($table_name = null, $schema_name = null)
    {
        if (!$this->_is_ddl_cache_allowed) {
            return $this;
        }
        if ($table_name === null) {
            $this->_ddl_cache = [];
            if ($this->_cache_adapter) {
                $this->_cache_adapter->clean(Cache_Constants::CLEANING_MODE_MATCHING_TAG, [self::DDL_CACHE_TAG]);
            }
        } else {
            $cache_key = $this->_get_table_name($table_name, $schema_name);
            $ddl_types = [self::DDL_DESCRIBE, self::DDL_CREATE, self::DDL_INDEX, self::DDL_FOREIGN_KEY, self::DDL_EXISTS];
            foreach ($ddl_types as $ddl_type) {
                unset($this->_ddl_cache[$ddl_type][$cache_key]);
            }
            if ($this->_cache_adapter) {
                foreach ($ddl_types as $ddl_type) {
                    $cache_id = $this->_get_cache_id($cache_key, $ddl_type);
                    $this->_cache_adapter->remove($cache_id);
                }
            }
        }
        return $this;
    }
    /**
     * Disallow DDL caching
     *
     * @return $this
     */
    public function disallow_ddl_cache()
    {
        $this->_is_ddl_cache_allowed = false;
        return $this;
    }
    /**
     * Allow DDL caching
     *
     * @return $this
     */
    public function allow_ddl_cache()
    {
        $this->_is_ddl_cache_allowed = true;
        return $this;
    }
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
    public function describe_table($table_name, $schema_name = null)
    {
        $cache_key = $this->_get_table_name($table_name, $schema_name);
        $ddl = $this->load_ddl_cache($cache_key, self::DDL_DESCRIBE);
        if ($ddl === false) {
            $ddl = $this->prepare_column_data(parent::describe_table($table_name, $schema_name));
            $this->save_ddl_cache($cache_key, self::DDL_DESCRIBE, $ddl);
        }
        return $ddl;
    }
    /**
     * Prepares column data for describeTable() method
     *
     * @param array $ddl
     * @return array
     */
    private function prepare_column_data(array $ddl): array
    {
        /**
         * Remove bug in some MySQL versions, when int-column without default value is described as:
         * having default empty string value
         */
        $affected = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'];
        foreach ($ddl as $key => $column_data) {
            if ($column_data['DEFAULT'] === '' && array_search($column_data['DATA_TYPE'], $affected) !== false) {
                $ddl[$key]['DEFAULT'] = null;
            }
        }
        foreach ($ddl as $key => $column_data) {
            $ddl[$key]['DATA_TYPE'] = $this->sanitize_column_data_type($column_data['DATA_TYPE']);
        }
        return $ddl;
    }
    /**
     * Format described column to definition, ready to be added to ddl table.
     *
     * Return array with keys: name, type, length, options, comment
     *
     * @param array $columnData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function get_column_create_by_describe($column_data)
    {
        $type = $this->_get_column_type_by_ddl($column_data);
        $options = [];
        if ($column_data['IDENTITY'] === true) {
            $options['identity'] = true;
        }
        if ($column_data['UNSIGNED'] === true) {
            $options['unsigned'] = true;
        }
        if ($column_data['NULLABLE'] === false && !($type == Table::TYPE_TEXT && isset($column_data['DEFAULT']) && strlen($column_data['DEFAULT']) != 0)) {
            $options['nullable'] = false;
        }
        if ($column_data['PRIMARY'] === true) {
            $options['primary'] = true;
        }
        if ($column_data['DEFAULT'] !== null && $type != Table::TYPE_TEXT) {
            $options['default'] = $this->quote($column_data['DEFAULT']);
        }
        if (isset($column_data['SCALE']) && strlen($column_data['SCALE']) > 0) {
            $options['scale'] = $column_data['SCALE'];
        }
        if (isset($column_data['PRECISION']) && strlen($column_data['PRECISION']) > 0) {
            $options['precision'] = $column_data['PRECISION'];
        }
        $comment = $this->string->upper_case_words($column_data['COLUMN_NAME'], '_', ' ');
        $result = ['name' => $column_data['COLUMN_NAME'], 'type' => $type, 'length' => $column_data['LENGTH'], 'options' => $options, 'comment' => $comment];
        return $result;
    }
    /**
     * Create \Magento\Framework\DB\Ddl\Table object by data from describe table
     *
     * @param string $tableName
     * @param string $newTableName
     * @return Table
     */
    public function create_table_by_ddl($table_name, $new_table_name)
    {
        $describe = $this->describe_table($table_name);
        $table = $this->new_table($new_table_name)->set_comment($this->string->upper_case_words($new_table_name, '_', ' '));
        foreach ($describe as $column_data) {
            $column_info = $this->get_column_create_by_describe($column_data);
            $table->add_column($column_info['name'], $column_info['type'], $column_info['length'], $column_info['options'], $column_info['comment']);
        }
        $indexes = $this->get_index_list($table_name);
        foreach ($indexes as $index_data) {
            /**
             * Do not create primary index - it is created with identity column.
             * For reliability check both name and type, because these values can start to differ in future.
             */
            if ($index_data['KEY_NAME'] == 'PRIMARY' || $index_data['INDEX_TYPE'] == Adapter_Interface::INDEX_TYPE_PRIMARY) {
                continue;
            }
            $fields = $index_data['COLUMNS_LIST'];
            $options = ['type' => $index_data['INDEX_TYPE']];
            $table->add_index($this->get_index_name($new_table_name, $fields, $index_data['INDEX_TYPE']), $fields, $options);
        }
        $foreign_keys = $this->get_foreign_keys($table_name);
        foreach ($foreign_keys as $key_data) {
            $fk_name = $this->get_foreign_key_name($new_table_name, $key_data['COLUMN_NAME'], $key_data['REF_TABLE_NAME'], $key_data['REF_COLUMN_NAME']);
            $on_delete = $this->_get_ddl_action($key_data['ON_DELETE']);
            $table->add_foreign_key($fk_name, $key_data['COLUMN_NAME'], $key_data['REF_TABLE_NAME'], $key_data['REF_COLUMN_NAME'], $on_delete);
        }
        // Set additional options
        $table_data = $this->show_table_status($table_name);
        $table->set_option('type', $table_data['Engine']);
        return $table;
    }
    /**
     * Modify the column definition by data from describe table
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return $this
     */
    public function modify_column_by_ddl($table_name, $column_name, $definition, $flush_data = false, $schema_name = null)
    {
        $definition = array_change_key_case($definition, CASE_UPPER);
        $definition['COLUMN_TYPE'] = $this->_get_column_type_by_ddl($definition);
        if (array_key_exists('DEFAULT', $definition) && $definition['DEFAULT'] === null) {
            unset($definition['DEFAULT']);
        }
        return $this->modify_column($table_name, $column_name, $definition, $flush_data, $schema_name);
    }
    /**
     * Retrieve column data type by data from describe table
     *
     * @param array $column
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _get_column_type_by_ddl($column)
    {
        // phpstan:ignore
        switch ($this->sanitize_column_data_type($column['DATA_TYPE'])) {
            case 'bool':
                return Table::TYPE_BOOLEAN;
            case 'tinytext':
            case 'char':
            case 'varchar':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return Table::TYPE_TEXT;
            case 'blob':
            case 'mediumblob':
            case 'longblob':
                return Table::TYPE_BLOB;
            case 'tinyint':
            case 'smallint':
                return Table::TYPE_SMALLINT;
            case 'mediumint':
            case 'int':
                return Table::TYPE_INTEGER;
            case 'bigint':
                return Table::TYPE_BIGINT;
            case 'datetime':
                return Table::TYPE_DATETIME;
            case 'timestamp':
                return Table::TYPE_TIMESTAMP;
            case 'date':
                return Table::TYPE_DATE;
            case 'float':
                return Table::TYPE_FLOAT;
            case 'decimal':
            case 'numeric':
                return Table::TYPE_DECIMAL;
        }
        return null;
    }
    /**
     * Remove old temporal format comment from column data type
     *
     * @param string $columnType
     * @return string
     */
    private function sanitize_column_data_type(string $column_type): string
    {
        /**
         * Starting from MariaDB 10.5.1 columns with old temporal formats are marked with a \/* mariadb-5.3 *\/
         * comment in the output of SHOW CREATE TABLE, SHOW COLUMNS, DESCRIBE statements,
         * as well as in the COLUMN_TYPE column of the INFORMATION_SCHEMA.COLUMNS Table.
         */
        return str_replace(' /* mariadb-5.3 */', '', $column_type);
    }
    /**
     * Change table storage engine
     *
     * @param string $tableName
     * @param string $engine
     * @param string $schemaName
     * @return \Zend_Db_Statement_Pdo
     */
    public function change_table_engine($table_name, $engine, $schema_name = null)
    {
        $table = $this->quote_identifier($this->_get_table_name($table_name, $schema_name));
        $sql = sprintf('ALTER TABLE %s ENGINE=%s', $table, $engine);
        return $this->raw_query($sql);
    }
    /**
     * Change table comment
     *
     * @param string $tableName
     * @param string $comment
     * @param string $schemaName
     * @return \Zend_Db_Statement_Pdo
     */
    public function change_table_comment($table_name, $comment, $schema_name = null)
    {
        $table = $this->quote_identifier($this->_get_table_name($table_name, $schema_name));
        $sql = sprintf("ALTER TABLE %s COMMENT='%s'", $table, $comment);
        return $this->raw_query($sql);
    }
    /**
     * Inserts a table row with specified data
     *
     * Special for Zero values to identity column
     *
     * @param string $table
     * @param array $bind
     * @return int The number of affected rows.
     */
    public function insert_force($table, array $bind)
    {
        $this->raw_query("SET @OLD_INSERT_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
        $result = $this->insert($table, $bind);
        $this->raw_query("SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'')");
        return $result;
    }
    /**
     * Inserts a table row with specified data.
     *
     * @param string $table The table to insert data into.
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function insert_on_duplicate($table, array $data, array $fields = [])
    {
        // extract and quote col names from the array keys
        $row = reset($data);
        // get first element from data array
        $bind = [];
        // SQL bind array
        $values = [];
        if (is_array($row)) {
            // Array of column-value pairs
            $cols = array_keys($row);
            foreach ($data as $row) {
                if (array_diff($cols, array_keys($row))) {
                    throw new \Zend_Db_Exception('Invalid data for insert');
                }
                $line = [];
                foreach ($cols as $field) {
                    $line[] = $row[$field];
                }
                $values[] = $this->_prepare_insert_data($line, $bind);
            }
            unset($row);
        } else {
            // Column-value pairs
            $cols = array_keys($data);
            $values[] = $this->_prepare_insert_data($data, $bind);
        }
        $update_fields = [];
        if (empty($fields)) {
            $fields = $cols;
        }
        // prepare ON DUPLICATE KEY conditions
        foreach ($fields as $k => $v) {
            $field = $value = null;
            if (!is_numeric($k)) {
                $field = $this->quote_identifier($k);
                if ($v instanceof \Zend_Db_Expr) {
                    $value = $v->__toString();
                } elseif ($v instanceof \Php_Db\Sql\Expression) {
                    $value = $v->get_expression();
                } elseif (is_string($v)) {
                    $value = sprintf('VALUES(%s)', $this->quote_identifier($v));
                } elseif (is_numeric($v)) {
                    $value = $this->quote_into('?', $v);
                }
            } elseif (is_string($v)) {
                $value = sprintf('VALUES(%s)', $this->quote_identifier($v));
                $field = $this->quote_identifier($v);
            }
            if ($field && is_string($value) && $value !== '') {
                $update_fields[] = sprintf('%s = %s', $field, $value);
            }
        }
        $insert_sql = $this->_get_insert_sql_query($table, $cols, $values);
        if ($update_fields) {
            $insert_sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $update_fields);
        }
        // execute the statement and return the number of affected rows
        $stmt = $this->query($insert_sql, array_values($bind));
        $result = $stmt->row_count();
        return $result;
    }
    /**
     * Inserts a table multiply rows with specified data.
     *
     * @param string|array|\Zend_Db_Expr $table The table to insert data into.
     * @param array $data Column-value pairs or array of Column-value pairs.
     * @return int The number of affected rows.
     * @throws \Zend_Db_Exception
     */
    public function insert_multiple($table, array $data)
    {
        $row = reset($data);
        // support insert syntaxes
        if (!is_array($row)) {
            return $this->insert($table, $data);
        }
        // validate data array
        $cols = array_keys($row);
        $insert_array = [];
        foreach ($data as $row) {
            $line = [];
            if (array_diff($cols, array_keys($row))) {
                throw new \Zend_Db_Exception('Invalid data for insert');
            }
            foreach ($cols as $field) {
                $line[] = $row[$field];
            }
            $insert_array[] = $line;
        }
        unset($row);
        return $this->insert_array($table, $cols, $insert_array);
    }
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
     * @param string $table
     * @param string[] $columns
     * @param array $data
     * @param int $strategy
     * @return int
     * @throws \Zend_Db_Exception
     */
    public function insert_array($table, array $columns, array $data, $strategy = 0)
    {
        $values = [];
        $bind = [];
        $columns_count = count($columns);
        foreach ($data as $row) {
            if (is_array($row) && $columns_count != count($row)) {
                throw new \Zend_Db_Exception('Invalid data for insert');
            }
            $values[] = $this->_prepare_insert_data($row, $bind);
        }
        switch ($strategy) {
            case self::REPLACE:
                $query = $this->_get_replace_sql_query($table, $columns, $values);
                break;
            default:
                $query = $this->_get_insert_sql_query($table, $columns, $values, $strategy);
        }
        // execute the statement and return the number of affected rows
        $stmt = $this->query($query, $bind);
        $result = $stmt->row_count();
        return $result;
    }
    /**
     * Set cache adapter
     *
     * @param FrontendInterface $cacheAdapter
     * @return $this
     */
    public function set_cache_adapter(Frontend_Interface $cache_adapter)
    {
        $this->_cache_adapter = $cache_adapter;
        return $this;
    }
    /**
     * Return new DDL Table object
     *
     * @param string $tableName the table name
     * @param string $schemaName the database/schema name
     * @return Table
     */
    public function new_table($table_name = null, $schema_name = null)
    {
        $table = new Table();
        if ($table_name !== null) {
            $table->set_name($table_name);
        }
        if ($schema_name !== null) {
            $table->set_schema($schema_name);
        }
        if (isset($this->_config['engine'])) {
            $table->set_option('type', $this->_config['engine']);
        }
        return $table;
    }
    /**
     * Create table
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Pdo
     */
    public function create_table(Table $table)
    {
        $this->get_schema_listener()->create_table($table);
        $columns = $table->get_columns();
        foreach ($columns as $column_entry) {
            if (empty($column_entry['COMMENT'])) {
                throw new \Zend_Db_Exception('Cannot create table without columns comments');
            }
        }
        $sql_fragment = array_merge($this->_get_columns_definition($table), $this->_get_indexes_definition($table), $this->_get_foreign_keys_definition($table));
        $table_options = $this->_get_options_definition($table);
        $sql = sprintf("CREATE TABLE IF NOT EXISTS %s (\n%s\n) %s", $this->quote_identifier($table->get_name()), implode(",\n", $sql_fragment), implode(' ', $table_options));
        if ($this->get_transaction_level() > 0) {
            $result = $this->create_connection()->query($sql);
        } else {
            $result = $this->query($sql);
        }
        $this->reset_ddl_cache($table->get_name(), $table->get_schema());
        return $result;
    }
    /**
     * Create temporary table
     *
     * @param \Magento\Framework\DB\Ddl\Table $table
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Pdo|void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function create_temporary_table(\Magento\Framework\DB\Ddl\Table $table)
    {
        $sql_fragment = array_merge($this->_get_columns_definition($table), $this->_get_indexes_definition($table), $this->_get_foreign_keys_definition($table));
        $table_options = $this->_get_options_definition($table);
        $sql = sprintf("CREATE TEMPORARY TABLE %s (\n%s\n) %s", $this->quote_identifier($table->get_name()), implode(",\n", $sql_fragment), implode(' ', $table_options));
        return $this->query($sql);
    }
    /**
     * Create temporary table like
     *
     * @param string $temporaryTableName
     * @param string $originTableName
     * @param bool $ifNotExists
     * @return \Zend_Db_Statement_Pdo
     */
    public function create_temporary_table_like($temporary_table_name, $origin_table_name, $if_not_exists = false)
    {
        $if_not_exists_sql = $if_not_exists ? ' IF NOT EXISTS' : '';
        $temporary_table = $this->quote_identifier($this->_get_table_name($temporary_table_name));
        $origin_table = $this->quote_identifier($this->_get_table_name($origin_table_name));
        $origin_create = $this->fetch_pairs("SHOW CREATE TABLE {$origin_table}");
        $sql = reset($origin_create);
        $sql = preg_replace('/\/\*!50100 TABLESPACE [^\s]+ \*\//', '', $sql);
        $sql = str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE' . $if_not_exists_sql, $sql);
        $sql = str_replace($origin_table, $temporary_table, $sql);
        return $this->query($sql);
    }
    /**
     * Rename several tables
     *
     * @param array $tablePairs array('oldName' => 'Name1', 'newName' => 'Name2')
     *
     * @return boolean
     * @throws \Zend_Db_Exception
     */
    public function rename_tables_batch(array $table_pairs)
    {
        if (count($table_pairs) == 0) {
            throw new \Zend_Db_Exception('Please provide tables for rename');
        }
        $renames_list = [];
        $tables_list = [];
        foreach ($table_pairs as $pair) {
            $old_table_name = $pair['oldName'];
            $new_table_name = $pair['newName'];
            $renames_list[] = sprintf('%s TO %s', $old_table_name, $new_table_name);
            $tables_list[$old_table_name] = $old_table_name;
            $tables_list[$new_table_name] = $new_table_name;
        }
        $query = sprintf('RENAME TABLE %s', implode(',', $renames_list));
        $this->query($query);
        foreach ($tables_list as $table) {
            $this->reset_ddl_cache($table);
        }
        return true;
    }
    /**
     * Retrieve columns and primary keys definition array for create table
     *
     * @param Table $table
     * @return string[]
     * @throws \Zend_Db_Exception
     */
    protected function _get_columns_definition(Table $table)
    {
        $definition = [];
        $primary = [];
        $columns = $table->get_columns();
        if (empty($columns)) {
            throw new \Zend_Db_Exception('Table columns are not defined');
        }
        foreach ($columns as $column_data) {
            $column_definition = $this->_get_column_definition($column_data);
            if ($column_data['PRIMARY']) {
                $primary[$column_data['COLUMN_NAME']] = $column_data['PRIMARY_POSITION'];
            }
            $definition[] = sprintf('  %s %s', $this->quote_identifier($column_data['COLUMN_NAME']), $column_definition);
        }
        // Set default collation to utf8mb4 for MySQL
        if (count($definition)) {
            foreach ($definition as $index => $column_definition) {
                $type = explode(' ', trim($column_definition));
                $definition[$index] = $this->set_default_charset_and_collation($type[1], $column_definition, 2);
            }
        }
        // PRIMARY KEY
        if (!empty($primary)) {
            asort($primary, SORT_NUMERIC);
            $primary = array_map([$this, 'quoteIdentifier'], array_keys($primary));
            $definition[] = sprintf('  PRIMARY KEY (%s)', implode(', ', $primary));
        }
        return $definition;
    }
    /**
     * Retrieve table indexes definition array for create table
     *
     * @param Table $table
     * @return string[]
     */
    protected function _get_indexes_definition(Table $table)
    {
        $definition = [];
        $indexes = $table->get_indexes();
        foreach ($indexes as $index_data) {
            if (!empty($index_data['TYPE'])) {
                //Skipping not supported fulltext indexes for NDB
                if ($index_data['TYPE'] == Adapter_Interface::INDEX_TYPE_FULLTEXT && $this->is_ndb($table)) {
                    continue;
                }
                switch ($index_data['TYPE']) {
                    case Adapter_Interface::INDEX_TYPE_PRIMARY:
                        $index_type = 'PRIMARY KEY';
                        unset($index_data['INDEX_NAME']);
                        break;
                    default:
                        $index_type = strtoupper($index_data['TYPE']);
                        break;
                }
            } else {
                $index_type = 'KEY';
            }
            $columns = [];
            foreach ($index_data['COLUMNS'] as $column_data) {
                $column = $this->quote_identifier($column_data['NAME']);
                if (!empty($column_data['SIZE'])) {
                    $column .= sprintf('(%d)', $column_data['SIZE']);
                }
                $columns[] = $column;
            }
            $index_name = isset($index_data['INDEX_NAME']) ? $this->quote_identifier($index_data['INDEX_NAME']) : '';
            $definition[] = sprintf('  %s %s (%s)', $index_type, $index_name, implode(', ', $columns));
        }
        return $definition;
    }
    /**
     * Check if NDB is used for table
     *
     * @param Table $table
     * @return bool
     */
    protected function is_ndb(Table $table)
    {
        $engine_type = strtolower($table->get_option('type') ?? '');
        return $engine_type == 'ndb' || $engine_type == 'ndbcluster';
    }
    /**
     * Retrieve table foreign keys definition array for create table
     *
     * @param Table $table
     * @return string[]
     */
    protected function _get_foreign_keys_definition(Table $table)
    {
        $definition = [];
        $relations = $table->get_foreign_keys();
        if (!empty($relations)) {
            foreach ($relations as $fk_data) {
                $on_delete = $this->_get_ddl_action($fk_data['ON_DELETE']);
                $definition[] = sprintf('  CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s', $this->quote_identifier($fk_data['FK_NAME']), $this->quote_identifier($fk_data['COLUMN_NAME']), $this->quote_identifier($fk_data['REF_TABLE_NAME']), $this->quote_identifier($fk_data['REF_COLUMN_NAME']), $on_delete);
            }
        }
        return $definition;
    }
    /**
     * Retrieve table options definition array for create table
     *
     * @param Table $table
     * @return string[]
     * @throws \Zend_Db_Exception
     */
    protected function _get_options_definition(Table $table)
    {
        $definition = [];
        $comment = $table->get_comment();
        if (empty($comment)) {
            throw new \Zend_Db_Exception('Comment for table is required and must be defined');
        }
        $definition[] = $this->quote_into('COMMENT=?', $comment);
        $table_props = ['type' => 'ENGINE=%s', 'checksum' => 'CHECKSUM=%d', 'auto_increment' => 'AUTO_INCREMENT=%d', 'avg_row_length' => 'AVG_ROW_LENGTH=%d', 'max_rows' => 'MAX_ROWS=%d', 'min_rows' => 'MIN_ROWS=%d', 'delay_key_write' => 'DELAY_KEY_WRITE=%d', 'row_format' => 'row_format=%s', 'charset' => 'charset=%s', 'collate' => 'COLLATE=%s'];
        foreach ($table_props as $key => $mask) {
            $v = $table->get_option($key);
            if ($v !== null) {
                $definition[] = sprintf($mask, $v);
            }
        }
        return $definition;
    }
    /**
     * Get column definition from description
     *
     * @param  array $options
     * @param  null|string $ddlType
     * @return string
     */
    public function get_column_definition_from_describe($options, $ddl_type = null)
    {
        $column_info = $this->get_column_create_by_describe($options);
        foreach ($column_info['options'] as $key => $value) {
            $column_info[$key] = $value;
        }
        return $this->_get_column_definition($column_info, $ddl_type);
    }
    /**
     * Retrieve column definition fragment
     *
     * @param array $options
     * @param string $ddlType Table DDL Column type constant
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return string
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    protected function _get_column_definition($options, $ddl_type = null)
    {
        // convert keys to uppercase
        $options = array_change_key_case($options, CASE_UPPER);
        $c_type = null;
        $c_unsigned = false;
        $c_nullable = true;
        $c_default = false;
        $c_identity = false;
        // detect and validate column type
        if ($ddl_type === null) {
            $ddl_type = $this->_get_ddl_type($options);
        } else {
            $ddl_type = $this->sanitize_column_data_type($ddl_type);
        }
        if (empty($ddl_type) || !isset($this->_ddl_column_types[$ddl_type])) {
            throw new \Zend_Db_Exception('Invalid column definition data');
        }
        // column size
        $c_type = $this->_ddl_column_types[$ddl_type];
        switch ($ddl_type) {
            case Table::TYPE_SMALLINT:
            case Table::TYPE_INTEGER:
            case Table::TYPE_BIGINT:
                if (!empty($options['UNSIGNED'])) {
                    $c_unsigned = true;
                }
                break;
            case Table::TYPE_DECIMAL:
            case Table::TYPE_FLOAT:
            case Table::TYPE_NUMERIC:
                $precision = 10;
                $scale = 0;
                $match = [];
                if (!empty($options['LENGTH']) && preg_match('#^\(?(\d+),(\d+)\)?$#', $options['LENGTH'], $match)) {
                    $precision = $match[1];
                    $scale = $match[2];
                } else {
                    if (isset($options['SCALE']) && is_numeric($options['SCALE'])) {
                        $scale = $options['SCALE'];
                    }
                    if (isset($options['PRECISION']) && is_numeric($options['PRECISION'])) {
                        $precision = $options['PRECISION'];
                    }
                }
                $c_type .= sprintf('(%d,%d)', $precision, $scale);
                if (!empty($options['UNSIGNED'])) {
                    $c_unsigned = true;
                }
                break;
            case Table::TYPE_TEXT:
            case Table::TYPE_BLOB:
            case Table::TYPE_VARBINARY:
                if (empty($options['LENGTH'])) {
                    $length = Table::DEFAULT_TEXT_SIZE;
                } else {
                    $length = $this->_parse_text_size($options['LENGTH']);
                }
                if ($length <= 255) {
                    $c_type = $ddl_type == Table::TYPE_TEXT ? 'varchar' : 'varbinary';
                    $c_type = sprintf('%s(%d)', $c_type, $length);
                } elseif ($length > 255 && $length <= 65536) {
                    $c_type = $ddl_type == Table::TYPE_TEXT ? 'text' : 'blob';
                } elseif ($length > 65536 && $length <= 16777216) {
                    $c_type = $ddl_type == Table::TYPE_TEXT ? 'mediumtext' : 'mediumblob';
                } else {
                    $c_type = $ddl_type == Table::TYPE_TEXT ? 'longtext' : 'longblob';
                }
                break;
        }
        if (array_key_exists('DEFAULT', $options)) {
            $c_default = $options['DEFAULT'];
        }
        if (array_key_exists('NULLABLE', $options)) {
            $c_nullable = (bool) $options['NULLABLE'];
        }
        if (!empty($options['IDENTITY']) || !empty($options['AUTO_INCREMENT'])) {
            $c_identity = true;
        }
        /*  For cases when tables created from createTableByDdl()
         *  where default value can be quoted already.
         *  We need to avoid "double-quoting" here
         */
        if ($c_default !== null && is_string($c_default) && strlen($c_default)) {
            $c_default = str_replace("'", '', $c_default);
        }
        // prepare default value string
        if ($ddl_type == Table::TYPE_TIMESTAMP) {
            if ($c_default === null) {
                $c_default = new \Zend_Db_Expr('NULL');
            } elseif ($c_default == Table::TIMESTAMP_INIT) {
                $c_default = new \Zend_Db_Expr('CURRENT_TIMESTAMP');
            } elseif ($c_default == Table::TIMESTAMP_UPDATE) {
                $c_default = new \Zend_Db_Expr('0 ON UPDATE CURRENT_TIMESTAMP');
            } elseif ($c_default == Table::TIMESTAMP_INIT_UPDATE) {
                $c_default = new \Zend_Db_Expr('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
            } elseif ($c_nullable && !$c_default) {
                $c_default = new \Zend_Db_Expr('NULL');
            } else {
                $c_default = false;
            }
        } elseif ($c_default === null && $c_nullable) {
            $c_default = new \Zend_Db_Expr('NULL');
        }
        if (empty($options['COMMENT'])) {
            $comment = '';
        } else {
            $comment = $options['COMMENT'];
        }
        //set column position
        $after = null;
        if (!empty($options['AFTER'])) {
            $after = $options['AFTER'];
        }
        return sprintf('%s%s%s%s%s COMMENT %s %s', $c_type, $c_unsigned ? ' UNSIGNED' : '', $c_nullable ? ' NULL' : ' NOT NULL', $c_default !== false ? $this->quote_into(' default ?', $c_default) : '', $c_identity ? ' auto_increment' : '', $this->quote($comment), $after ? 'AFTER ' . $this->quote_identifier($after) : '');
    }
    /**
     * Drop table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return true
     */
    public function drop_table($table_name, $schema_name = null)
    {
        $table = $this->quote_identifier($this->_get_table_name($table_name, $schema_name));
        $query = 'DROP TABLE IF EXISTS ' . $table;
        if ($this->get_transaction_level() > 0) {
            $this->create_connection()->query($query);
        } else {
            $this->query($query);
        }
        $this->reset_ddl_cache($table_name, $schema_name);
        $this->get_schema_listener()->drop_table($table_name);
        return true;
    }
    /**
     * Drop temporary table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function drop_temporary_table($table_name, $schema_name = null)
    {
        $table = $this->quote_identifier($this->_get_table_name($table_name, $schema_name));
        $query = 'DROP TEMPORARY TABLE IF EXISTS ' . $table;
        $this->query($query);
        return true;
    }
    /**
     * Truncate a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function truncate_table($table_name, $schema_name = null)
    {
        if (!$this->is_table_exists($table_name, $schema_name)) {
            throw new \Zend_Db_Exception(sprintf('Table "%s" does not exist', $table_name));
        }
        $table = $this->quote_identifier($this->_get_table_name($table_name, $schema_name));
        $query = 'TRUNCATE TABLE ' . $table;
        $this->query($query);
        return $this;
    }
    /**
     * Check is a table exists
     *
     * @param string $tableName
     * @param string $schemaName
     * @return bool
     */
    public function is_table_exists($table_name, $schema_name = null)
    {
        $cache_key = $this->_get_table_name($table_name, $schema_name);
        $ddl = $this->load_ddl_cache($cache_key, self::DDL_EXISTS);
        if ($ddl !== false) {
            return true;
        }
        $from_db_name = 'DATABASE()';
        if ($schema_name !== null) {
            $from_db_name = $this->quote($schema_name);
        }
        $sql = sprintf('SELECT COUNT(1) AS tbl_exists FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = %s AND TABLE_SCHEMA = %s', $this->quote($table_name), $from_db_name);
        $ddl = $this->raw_fetch_row($sql, 'tbl_exists');
        if ($ddl) {
            $this->save_ddl_cache($cache_key, self::DDL_EXISTS, $ddl);
            return true;
        }
        return false;
    }
    /**
     * Rename table
     *
     * @param string $oldTableName
     * @param string $newTableName
     * @param string $schemaName
     * @return true
     * @throws \Zend_Db_Exception
     */
    public function rename_table($old_table_name, $new_table_name, $schema_name = null)
    {
        if (!$this->is_table_exists($old_table_name, $schema_name)) {
            throw new \Zend_Db_Exception(sprintf('Table "%s" does not exist', $old_table_name));
        }
        if ($this->is_table_exists($new_table_name, $schema_name)) {
            throw new \Zend_Db_Exception(sprintf('Table "%s" already exists', $new_table_name));
        }
        $this->get_schema_listener()->rename_table($old_table_name, $new_table_name);
        $old_table = $this->_get_table_name($old_table_name, $schema_name);
        $new_table = $this->_get_table_name($new_table_name, $schema_name);
        $query = sprintf('ALTER TABLE %s RENAME TO %s', $old_table, $new_table);
        if ($this->get_transaction_level() > 0) {
            $this->create_connection()->query($query);
        } else {
            $this->query($query);
        }
        $this->reset_ddl_cache($old_table_name, $schema_name);
        return true;
    }
    /**
     * Add new index to table name
     *
     * @param string $tableName
     * @param string $indexName
     * @param string|array $fields the table column name or array of ones
     * @param string $indexType the index type
     * @param string $schemaName
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function add_index($table_name, $index_name, $fields, $index_type = Adapter_Interface::INDEX_TYPE_INDEX, $schema_name = null)
    {
        $this->get_schema_listener()->add_index($table_name, $index_name, $fields, $index_type);
        $columns = $this->describe_table($table_name, $schema_name);
        $key_list = $this->get_index_list($table_name, $schema_name);
        $query = sprintf('ALTER TABLE %s', $this->quote_identifier($this->_get_table_name($table_name, $schema_name)));
        if (isset($key_list[strtoupper($index_name)])) {
            if ($key_list[strtoupper($index_name)]['INDEX_TYPE'] == Adapter_Interface::INDEX_TYPE_PRIMARY) {
                $query .= ' DROP PRIMARY KEY,';
            } else {
                $query .= sprintf(' DROP INDEX %s,', $this->quote_identifier($index_name));
            }
        }
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $field_sql = [];
        foreach ($fields as $field) {
            if (!isset($columns[$field])) {
                $msg = sprintf('There is no field "%s" that you are trying to create an index on "%s"', $field, $table_name);
                throw new \Zend_Db_Exception($msg);
            }
            $field_sql[] = $this->quote_identifier($field);
        }
        $field_sql = implode(',', $field_sql);
        switch (strtolower((string) $index_type)) {
            case Adapter_Interface::INDEX_TYPE_PRIMARY:
                $condition = 'PRIMARY KEY';
                break;
            case Adapter_Interface::INDEX_TYPE_UNIQUE:
                $condition = 'UNIQUE ' . $this->quote_identifier($index_name);
                break;
            case Adapter_Interface::INDEX_TYPE_FULLTEXT:
                $condition = 'FULLTEXT ' . $this->quote_identifier($index_name);
                break;
            default:
                $condition = 'INDEX ' . $this->quote_identifier($index_name);
                break;
        }
        $query .= sprintf(' ADD %s (%s)', $condition, $field_sql);
        $cycle = true;
        while ($cycle === true) {
            try {
                $result = $this->raw_query($query);
                $cycle = false;
            } catch (\Exception $e) {
                if ($index_type !== null && in_array(strtolower($index_type), ['primary', 'unique'])) {
                    $match = [];
                    // phpstan:ignore
                    if (preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\']+\'([\d.-]+)\'#', $e->get_message(), $match)) {
                        $ids = explode('-', $match[1]);
                        $this->_remove_duplicate_entry($table_name, $fields, $ids);
                        continue;
                    }
                }
                throw $e;
            }
        }
        $this->reset_ddl_cache($table_name, $schema_name);
        // @phpstan-ignore-next-line
        return $result;
    }
    /**
     * Drop the index from table
     *
     * @param string $tableName
     * @param string $keyName
     * @param string $schemaName
     * @return true|\Zend_Db_Statement_Interface
     */
    public function drop_index($table_name, $key_name, $schema_name = null)
    {
        $index_list = $this->get_index_list($table_name, $schema_name);
        $index_type = 'index';
        $key_name = $key_name !== null ? strtoupper($key_name) : '';
        if (!isset($index_list[$key_name])) {
            return true;
        }
        if ($key_name == 'PRIMARY') {
            $index_type = 'primary';
            $cond = 'DROP PRIMARY KEY';
        } else {
            if (strpos($key_name, 'UNQ_') !== false) {
                $index_type = 'unique';
            }
            $cond = 'DROP KEY ' . $this->quote_identifier($index_list[$key_name]['KEY_NAME']);
        }
        $sql = sprintf('ALTER TABLE %s %s', $this->quote_identifier($this->_get_table_name($table_name, $schema_name)), $cond);
        $this->get_schema_listener()->drop_index($table_name, $key_name, $index_type);
        $this->reset_ddl_cache($table_name, $schema_name);
        return $this->raw_query($sql);
    }
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
     * @param bool $purge trying remove invalid data
     * @param string $schemaName
     * @param string $refSchemaName
     * @return \Zend_Db_Statement_Interface
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function add_foreign_key($fk_name, $table_name, $column_name, $ref_table_name, $ref_column_name, $on_delete = Adapter_Interface::FK_ACTION_CASCADE, $purge = false, $schema_name = null, $ref_schema_name = null)
    {
        $this->drop_foreign_key($table_name, $fk_name, $schema_name);
        if ($purge) {
            $this->purge_orphan_records($table_name, $column_name, $ref_table_name, $ref_column_name, $on_delete);
        }
        $query = sprintf('ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s)', $this->quote_identifier($this->_get_table_name($table_name, $schema_name)), $this->quote_identifier($fk_name), $this->quote_identifier($column_name), $this->quote_identifier($this->_get_table_name($ref_table_name, $ref_schema_name)), $this->quote_identifier($ref_column_name));
        if ($on_delete !== null) {
            $query .= ' ON DELETE ' . strtoupper($on_delete);
        }
        $this->get_schema_listener()->add_foreign_key($fk_name, $table_name, $column_name, $ref_table_name, $ref_column_name, $on_delete);
        $result = $this->raw_query($query);
        $this->reset_ddl_cache($table_name);
        return $result;
    }
    /**
     * Format Date to internal database date format
     *
     * @param int|string|\DateTimeInterface $date
     * @param bool $includeTime
     * @return \Zend_Db_Expr
     */
    public function format_date($date, $include_time = true)
    {
        $date = $this->date_time->format_date($date, $include_time);
        if ($date === null) {
            return new \Zend_Db_Expr('NULL');
        }
        return new \Zend_Db_Expr($this->quote($date));
    }
    /**
     * Run additional environment before setup
     *
     * @return $this
     */
    public function start_setup()
    {
        $this->raw_query("SET SQL_MODE=''");
        $this->raw_query('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0');
        $this->raw_query("SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
        $this->mysqlversion = $this->fetch_pairs("SHOW variables LIKE 'version'")['version'] ?? '';
        if ($this->is_mysql8engine_used() && str_contains($this->mysqlversion, '8.4')) {
            $this->raw_query('SET @OLD_RESTRICT_FK_ON_NON_STANDARD_KEY=@@RESTRICT_FK_ON_NON_STANDARD_KEY');
            $this->raw_query('SET RESTRICT_FK_ON_NON_STANDARD_KEY=0');
        }
        return $this;
    }
    /**
     * Run additional environment after setup
     *
     * @return $this
     */
    public function end_setup()
    {
        $this->raw_query("SET SQL_MODE=IFNULL(@OLD_SQL_MODE,'')");
        $this->raw_query('SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS=0, 0, 1)');
        if ($this->is_mysql8engine_used() && str_contains($this->mysqlversion, '8.4')) {
            $this->raw_query('SET RESTRICT_FK_ON_NON_STANDARD_KEY=IF(@OLD_RESTRICT_FK_ON_NON_STANDARD_KEY=0, 0, 1)');
        }
        return $this;
    }
    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array is - one of the following structures is expected:
     * - array("from" => $fromValue, "to" => $toValue)
     * - array("eq" => $equalValue)
     * - array("neq" => $notEqualValue)
     * - array("like" => $likeValue)
     * - array("in" => array($inValues))
     * - array("nin" => array($notInValues))
     * - array("notnull" => $valueIsNotNull)
     * - array("null" => $valueIsNull)
     * - array("gt" => $greaterValue)
     * - array("lt" => $lessValue)
     * - array("gteq" => $greaterOrEqualValue)
     * - array("lteq" => $lessOrEqualValue)
     * - array("finset" => $valueInSet)
     * - array("nfinset" => $valueNotInSet)
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepare_sql_condition($field_name, $condition)
    {
        $condition_key_map = ['eq' => '{{fieldName}} = ?', 'neq' => '{{fieldName}} != ?', 'like' => '{{fieldName}} LIKE ?', 'nlike' => '{{fieldName}} NOT LIKE ?', 'in' => '{{fieldName}} IN(?)', 'nin' => '{{fieldName}} NOT IN(?)', 'is' => '{{fieldName}} IS ?', 'notnull' => '{{fieldName}} IS NOT NULL', 'null' => '{{fieldName}} IS NULL', 'gt' => '{{fieldName}} > ?', 'lt' => '{{fieldName}} < ?', 'gteq' => '{{fieldName}} >= ?', 'lteq' => '{{fieldName}} <= ?', 'finset' => 'FIND_IN_SET(?, {{fieldName}})', 'nfinset' => 'NOT FIND_IN_SET(?, {{fieldName}})', 'regexp' => '{{fieldName}} REGEXP ?', 'from' => '{{fieldName}} >= ?', 'to' => '{{fieldName}} <= ?', 'seq' => null, 'sneq' => null, 'ntoa' => 'INET_NTOA({{fieldName}}) LIKE ?'];
        $query = '';
        if (is_array($condition)) {
            $key = key(array_intersect_key($condition, $condition_key_map)) ?? '';
            if (isset($condition['from']) || isset($condition['to'])) {
                if (isset($condition['from'])) {
                    $from = $this->_prepare_sql_date_condition($condition, 'from');
                    $query = $this->_prepare_quoted_sql_condition($condition_key_map['from'], $from, $field_name);
                }
                if (isset($condition['to'])) {
                    $query .= empty($query) ? '' : ' AND ';
                    $to = $this->_prepare_sql_date_condition($condition, 'to');
                    $query = $query . $this->_prepare_quoted_sql_condition($condition_key_map['to'], $to, $field_name);
                }
            } elseif (array_key_exists($key, $condition_key_map)) {
                $value = $condition[$key];
                if ($key == 'seq' || $key == 'sneq') {
                    $key = $this->_transform_string_sql_condition($key, $value);
                }
                if (($key == 'in' || $key == 'nin') && is_string($value)) {
                    $value = explode(',', $value);
                }
                $query = $this->_prepare_quoted_sql_condition($condition_key_map[$key], $value, $field_name);
            } else {
                $queries = [];
                foreach ($condition as $or_condition) {
                    $queries[] = sprintf('(%s)', $this->prepare_sql_condition($field_name, $or_condition));
                }
                $query = sprintf('(%s)', implode(' OR ', $queries));
            }
        } else {
            $query = $this->_prepare_quoted_sql_condition($condition_key_map['eq'], (string) $condition, $field_name);
        }
        return $query;
    }
    /**
     * Prepare Sql condition
     *
     * @param  string $text Condition value
     * @param  mixed $value
     * @param  string $fieldName
     * @return string
     */
    protected function _prepare_quoted_sql_condition($text, $value, $field_name)
    {
        $text = str_replace('{{fieldName}}', (string) $field_name, (string) $text);
        $sql = $this->quote_into($text, $value);
        return $sql;
    }
    /**
     * Transforms sql condition key 'seq' / 'sneq' that is used for comparing string values to its analog:
     * - 'null' / 'notnull' for empty strings
     * - 'eq' / 'neq' for non-empty strings
     *
     * @param string $conditionKey
     * @param mixed $value
     * @return string
     */
    protected function _transform_string_sql_condition($condition_key, $value)
    {
        $value = (string) $value;
        if ($value == '') {
            return $condition_key == 'seq' ? 'null' : 'notnull';
        } else {
            return $condition_key == 'seq' ? 'eq' : 'neq';
        }
    }
    /**
     * Prepare value for save in column
     *
     * Return converted to column data type value
     *
     * @param array $column the column describe array
     * @param mixed $value
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function prepare_column_value(array $column, $value)
    {
        if ($value instanceof \Zend_Db_Expr) {
            return $value;
        }
        if ($value instanceof Parameter) {
            return $value;
        }
        $column['DATA_TYPE'] = $this->sanitize_column_data_type($column['DATA_TYPE']);
        // return original value if invalid column describe data
        if (!isset($column['DATA_TYPE'])) {
            return $value;
        }
        // return null
        if ($value === null && $column['NULLABLE']) {
            return null;
        }
        switch ($column['DATA_TYPE']) {
            case 'smallint':
            case 'int':
                $value = (int) $value;
                break;
            case 'bigint':
                if (!is_integer($value)) {
                    $value = sprintf('%.0f', (float) $value);
                }
                break;
            case 'decimal':
                $precision = 10;
                $scale = 0;
                if (isset($column['SCALE'])) {
                    $scale = $column['SCALE'];
                }
                if (isset($column['PRECISION'])) {
                    $precision = $column['PRECISION'];
                }
                $format = sprintf('%%%d.%dF', $precision - $scale, $scale);
                $value = (float) sprintf($format, $value);
                break;
            case 'float':
                $value = (float) sprintf('%F', $value);
                break;
            case 'date':
                $value = $this->format_date($value, false);
                break;
            case 'datetime':
            case 'timestamp':
                $value = $this->format_date($value);
                break;
            case 'varchar':
            case 'mediumtext':
            case 'text':
            case 'longtext':
                $value = (string) $value;
                if ($column['NULLABLE'] && $value == '') {
                    $value = null;
                }
                break;
            case 'varbinary':
            case 'mediumblob':
            case 'blob':
            case 'longblob':
                // No special processing for MySQL is needed
                break;
        }
        return $value;
    }
    /**
     * Generate fragment of SQL, that check condition and return true or false value
     *
     * @param \Zend_Db_Expr|\Magento\Framework\DB\Select|string $expression
     * @param string $true true value
     * @param string $false false value
     * @return \Zend_Db_Expr
     */
    public function get_check_sql($expression, $true, $false)
    {
        if ($expression instanceof \Zend_Db_Expr || $expression instanceof \Zend_Db_Select) {
            $expression = sprintf('IF((%s), %s, %s)', $expression, $true, $false);
        } else {
            $expression = sprintf('IF(%s, %s, %s)', $expression, $true, $false);
        }
        return new \Zend_Db_Expr($expression);
    }
    /**
     * Returns valid IFNULL expression
     *
     * @param \Zend_Db_Expr|\Magento\Framework\DB\Select|string $expression
     * @param string|int $value OPTIONAL. Applies when $expression is NULL
     * @return \Zend_Db_Expr
     */
    public function get_if_null_sql($expression, $value = 0)
    {
        if ($expression instanceof \Zend_Db_Expr || $expression instanceof \Zend_Db_Select) {
            $expression = sprintf('IFNULL((%s), %s)', $expression, $value);
        } else {
            $expression = sprintf('IFNULL(%s, %s)', $expression, $value);
        }
        return new \Zend_Db_Expr($expression);
    }
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
    public function get_case_sql($value_name, $cases_results, $default_value = null)
    {
        $expression = 'CASE ' . $value_name;
        foreach ($cases_results as $case => $result) {
            $expression .= ' WHEN ' . $case . ' THEN ' . $result;
        }
        if ($default_value !== null) {
            $expression .= ' ELSE ' . $default_value;
        }
        $expression .= ' END';
        return new \Zend_Db_Expr($expression);
    }
    /**
     * Generate fragment of SQL, that combine together (concatenate) the results from data array
     *
     * All arguments in data must be quoted
     *
     * @param string[] $data
     * @param string $separator concatenate with separator
     * @return \Zend_Db_Expr
     */
    public function get_concat_sql(array $data, $separator = null)
    {
        $format = empty($separator) ? 'CONCAT(%s)' : "CONCAT_WS('{$separator}', %s)";
        return new \Zend_Db_Expr(sprintf($format, implode(', ', $data)));
    }
    /**
     * Generate fragment of SQL that returns length of character string
     *
     * The string argument must be quoted
     *
     * @param string $string
     * @return \Zend_Db_Expr
     */
    public function get_length_sql($string)
    {
        return new \Zend_Db_Expr(sprintf('LENGTH(%s)', $string));
    }
    /**
     * Generate least SQL fragment
     *
     * Generate fragment of SQL, that compare with two or more arguments, and returns the smallest
     * (minimum-valued) argument
     * All arguments in data must be quoted
     *
     * @param string[] $data
     * @return \Zend_Db_Expr
     */
    public function get_least_sql(array $data)
    {
        return new \Zend_Db_Expr(sprintf('LEAST(%s)', implode(', ', $data)));
    }
    /**
     * Generate greatest SQL fragment
     *
     * Generate fragment of SQL, that compare with two or more arguments, and returns the largest
     * (maximum-valued) argument
     * All arguments in data must be quoted
     *
     * @param string[] $data
     * @return \Zend_Db_Expr
     */
    public function get_greatest_sql(array $data)
    {
        return new \Zend_Db_Expr(sprintf('GREATEST(%s)', implode(', ', $data)));
    }
    /**
     * Get Interval Unit SQL fragment
     *
     * @param int $interval
     * @param string $unit
     * @return string
     * @throws \Zend_Db_Exception
     */
    protected function _get_interval_unit_sql($interval, $unit)
    {
        if (!isset($this->_interval_units[$unit])) {
            throw new \Zend_Db_Exception(sprintf('Undefined interval unit "%s" specified', $unit));
        }
        return sprintf('INTERVAL %d %s', $interval, $this->_interval_units[$unit]);
    }
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
    public function get_date_add_sql($date, $interval, $unit)
    {
        $expr = sprintf('DATE_ADD(%s, %s)', $date, $this->_get_interval_unit_sql($interval, $unit));
        return new \Zend_Db_Expr($expr);
    }
    /**
     * Subtract time values (intervals) to a date value
     *
     * @see INTERVAL_* constants for $expr
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param int|string $interval
     * @param string $unit
     * @return \Zend_Db_Expr
     */
    public function get_date_sub_sql($date, $interval, $unit)
    {
        $expr = sprintf('DATE_SUB(%s, %s)', $date, $this->_get_interval_unit_sql($interval, $unit));
        return new \Zend_Db_Expr($expr);
    }
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
     * @param string $date quoted date value or non quoted SQL statement(field)
     * @param string $format
     * @return \Zend_Db_Expr
     */
    public function get_date_format_sql($date, $format)
    {
        $expr = sprintf("DATE_FORMAT(%s, '%s')", $date, $format);
        return new \Zend_Db_Expr($expr);
    }
    /**
     * Extract the date part of a date or datetime expression
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @return \Zend_Db_Expr
     */
    public function get_date_part_sql($date)
    {
        return new \Zend_Db_Expr(sprintf('DATE(%s)', $date));
    }
    /**
     * Prepare substring sql function
     *
     * @param \Zend_Db_Expr|string $stringExpression quoted field name or SQL statement
     * @param int|string|\Zend_Db_Expr $pos
     * @param int|string|\Zend_Db_Expr|null $len
     * @return \Zend_Db_Expr
     */
    public function get_substring_sql($string_expression, $pos, $len = null)
    {
        if ($len === null) {
            return new \Zend_Db_Expr(sprintf('SUBSTRING(%s, %s)', $string_expression, $pos));
        }
        return new \Zend_Db_Expr(sprintf('SUBSTRING(%s, %s, %s)', $string_expression, $pos, $len));
    }
    /**
     * Prepare standard deviation sql function
     *
     * @param \Zend_Db_Expr|string $expressionField quoted field name or SQL statement
     * @return \Zend_Db_Expr
     */
    public function get_standard_deviation_sql($expression_field)
    {
        return new \Zend_Db_Expr(sprintf('STDDEV_SAMP(%s)', $expression_field));
    }
    /**
     * Extract part of a date
     *
     * @see INTERVAL_* constants for $unit
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param string $unit
     * @return \Zend_Db_Expr
     * @throws \Zend_Db_Exception
     */
    public function get_date_extract_sql($date, $unit)
    {
        if (!isset($this->_interval_units[$unit])) {
            throw new \Zend_Db_Exception(sprintf('Undefined interval unit "%s" specified', $unit));
        }
        $expr = sprintf('EXTRACT(%s FROM %s)', $this->_interval_units[$unit], $date);
        return new \Zend_Db_Expr($expr);
    }
    /**
     * Returns a compressed version of the table name if it is too long
     *
     * @param string $tableName
     * @return string
     * @codeCoverageIgnore
     */
    public function get_table_name($table_name)
    {
        return Expression_Converter::shorten_entity_name($table_name, 't_');
    }
    /**
     * Build a trigger name based on table name and trigger details
     *
     * @param string $tableName The table which is the subject of the trigger
     * @param string $time Either "before" or "after"
     * @param string $event The DB level event which activates the trigger, i.e. "update" or "insert"
     * @return string
     * @codeCoverageIgnore
     */
    public function get_trigger_name($table_name, $time, $event)
    {
        $trigger_name = 'trg_' . $table_name . '_' . $time . '_' . $event;
        return Expression_Converter::shorten_entity_name($trigger_name, 'trg_');
    }
    /**
     * Retrieve valid index name
     *
     * Check index name length and allowed symbols
     *
     * @param string $tableName
     * @param string|string[] $fields the columns list
     * @param string $indexType
     * @return string
     */
    public function get_index_name($table_name, $fields, $index_type = '')
    {
        if (is_array($fields)) {
            $fields = implode('_', $fields);
        }
        switch (strtolower((string) $index_type)) {
            case Adapter_Interface::INDEX_TYPE_UNIQUE:
                $prefix = 'unq_';
                break;
            case Adapter_Interface::INDEX_TYPE_FULLTEXT:
                $prefix = 'fti_';
                break;
            case Adapter_Interface::INDEX_TYPE_INDEX:
            default:
                $prefix = 'idx_';
        }
        return strtoupper(Expression_Converter::shorten_entity_name($table_name . '_' . $fields, $prefix));
    }
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
     * @codeCoverageIgnore
     */
    public function get_foreign_key_name($pri_table_name, $pri_column_name, $ref_table_name, $ref_column_name)
    {
        $fk_name = sprintf('%s_%s_%s_%s', $pri_table_name, $pri_column_name, $ref_table_name, $ref_column_name);
        return strtoupper(Expression_Converter::shorten_entity_name($fk_name, 'fk_'));
    }
    /**
     * Stop updating indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return $this
     */
    public function disable_table_keys($table_name, $schema_name = null)
    {
        $table_name = $this->_get_table_name($table_name, $schema_name);
        $query = sprintf('ALTER TABLE %s DISABLE KEYS', $this->quote_identifier($table_name));
        $this->query($query);
        return $this;
    }
    /**
     * Re-create missing indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return $this
     */
    public function enable_table_keys($table_name, $schema_name = null)
    {
        $table_name = $this->_get_table_name($table_name, $schema_name);
        $query = sprintf('ALTER TABLE %s ENABLE KEYS', $this->quote_identifier($table_name));
        $this->query($query);
        return $this;
    }
    /**
     * Get insert from Select object query
     *
     * @param Select $select
     * @param string $table insert into table
     * @param array $fields
     * @param int|false $mode
     * @return string
     */
    public function insert_from_select(Select $select, $table, array $fields = [], $mode = false)
    {
        $query = $mode === self::REPLACE ? 'REPLACE' : 'INSERT';
        if ($mode === self::INSERT_IGNORE) {
            $query .= ' IGNORE';
        }
        $query = sprintf('%s INTO %s', $query, $this->quote_identifier($table));
        $count_fields_in_select = count($select->get_part(Select::COLUMNS));
        if (empty($fields) && $count_fields_in_select > 1) {
            $fields = array_slice(array_keys($this->describe_table($table)), 0, $count_fields_in_select);
        }
        if ($fields) {
            $columns = array_map([$this, 'quoteIdentifier'], $fields);
            $query = sprintf('%s (%s)', $query, join(', ', $columns));
        }
        $query = sprintf('%s %s', $query, $select->assemble());
        if ($mode === self::INSERT_ON_DUPLICATE) {
            $query .= $this->render_on_duplicate($table, $fields);
        }
        return $query;
    }
    /**
     * Render On Duplicate query part
     *
     * @param string $table
     * @param array $fields
     * @return string
     */
    private function render_on_duplicate($table, array $fields)
    {
        if (!$fields) {
            $describe = $this->describe_table($table);
            foreach ($describe as $column) {
                if ($column['PRIMARY'] === false) {
                    $fields[] = $column['COLUMN_NAME'];
                }
            }
        }
        $update = [];
        foreach ($fields as $field) {
            $update[] = sprintf('%1$s = VALUES(%1$s)', $this->quote_identifier($field));
        }
        return count($update) ? ' ON DUPLICATE KEY UPDATE ' . join(', ', $update) : '';
    }
    /**
     * Get insert queries in array for insert by range with step parameter
     *
     * @param string $rangeField
     * @param \Magento\Framework\DB\Select $select
     * @param int $stepCount
     * @return \Magento\Framework\DB\Select[]
     * @throws LocalizedException
     * @deprecated 100.1.3
     * @see MAGETWO-55589
     */
    public function selects_by_range($range_field, \Magento\Framework\DB\Select $select, $step_count = 100)
    {
        $iterator = $this->get_query_generator()->generate($range_field, $select, $step_count);
        $queries = [];
        foreach ($iterator as $query) {
            $queries[] = $query;
        }
        return $queries;
    }
    /**
     * Get query generator
     *
     * @return QueryGenerator
     * @deprecated 100.1.3
     * @see MAGETWO-55589
     */
    private function get_query_generator()
    {
        if ($this->query_generator === null) {
            // phpcs:ignore Magento2.PHP.AutogeneratedClassNotInConstructor
            $this->query_generator = \Magento\Framework\App\Object_Manager::get_instance()->create(Query_Generator::class);
        }
        return $this->query_generator;
    }
    /**
     * Get update table query using select object for join and update
     *
     * @param Select $select
     * @param string|array $table
     * @return string
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function update_from_select(Select $select, $table)
    {
        if (!is_array($table)) {
            $table = [$table => $table];
        }
        // get table name and alias
        $keys = array_keys($table);
        $table_alias = $keys[0];
        $table_name = $table[$keys[0]];
        $query = sprintf('UPDATE %s', $this->quote_table_as($table_name, $table_alias));
        // render JOIN conditions (FROM Part)
        $join_conds = [];
        foreach ($select->get_part(\Magento\Framework\DB\Select::FROM) as $correlation_name => $join_prop) {
            if ($join_prop['joinType'] == \Magento\Framework\DB\Select::FROM) {
                $join_type = strtoupper(\Magento\Framework\DB\Select::INNER_JOIN);
            } else {
                $join_type = strtoupper($join_prop['joinType']);
            }
            $join_table = '';
            if ($join_prop['schema'] !== null) {
                $join_table = sprintf('%s.', $this->quote_identifier($join_prop['schema']));
            }
            $join_table .= $this->quote_table_as($join_prop['tableName'], $correlation_name);
            $join = sprintf(' %s %s', $join_type, $join_table);
            if (!empty($join_prop['joinCondition'])) {
                $join = sprintf('%s ON %s', $join, $join_prop['joinCondition']);
            }
            $join_conds[] = $join;
        }
        if ($join_conds) {
            $query = sprintf("%s\n%s", $query, implode("\n", $join_conds));
        }
        // render UPDATE SET
        $columns = [];
        foreach ($select->get_part(\Magento\Framework\DB\Select::COLUMNS) as $column_entry) {
            list($correlation_name, $column, $alias) = $column_entry;
            if (empty($alias)) {
                $alias = $column;
            }
            if (!$column instanceof \Zend_Db_Expr && !empty($correlation_name)) {
                $column = $this->quote_identifier([$correlation_name, $column]);
            }
            $columns[] = sprintf('%s = %s', $this->quote_identifier([$table_alias, $alias]), $column);
        }
        if (!$columns) {
            throw new Localized_Exception(new \Magento\Framework\Phrase('The columns for UPDATE statement are not defined'));
        }
        $query = sprintf("%s\nSET %s", $query, implode(', ', $columns));
        // render WHERE
        $where_part = $select->get_part(\Magento\Framework\DB\Select::WHERE);
        if ($where_part) {
            $query = sprintf("%s\nWHERE %s", $query, implode(' ', $where_part));
        }
        return $query;
    }
    /**
     * Get delete from select object query
     *
     * @param Select $select
     * @param string $table the table name or alias used in select
     * @return string
     */
    public function delete_from_select(Select $select, $table)
    {
        $select = clone $select;
        $select->reset(\Magento\Framework\DB\Select::DISTINCT);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $query = sprintf('DELETE %s %s', $this->quote_identifier($table), $select->assemble());
        return $query;
    }
    /**
     * Calculate checksum for table or for group of tables
     *
     * @param array|string $tableNames array of tables names | table name
     * @param string $schemaName schema name
     * @return array
     */
    public function get_tables_checksum($table_names, $schema_name = null)
    {
        $result = [];
        $table_names = is_array($table_names) ? $table_names : [$table_names];
        foreach ($table_names as $table_name) {
            $query = 'CHECKSUM TABLE ' . $this->_get_table_name($table_name, $schema_name);
            $check_sum_array = $this->fetch_row($query);
            $result[$table_name] = $check_sum_array['Checksum'];
        }
        return $result;
    }
    /**
     * Check if the database support STRAIGHT JOIN
     *
     * @return true
     */
    public function support_straight_join()
    {
        return true;
    }
    /**
     * Adds order by random to select object
     *
     * Possible using integer field for optimization
     *
     * @param Select $select
     * @param string $field
     * @return $this
     */
    public function order_rand(Select $select, $field = null)
    {
        if ($field !== null) {
            $expression = new \Zend_Db_Expr(sprintf('RAND() * %s', $this->quote_identifier($field)));
            $select->columns(['mage_rand' => $expression]);
            $spec = new \Zend_Db_Expr('mage_rand');
        } else {
            $spec = new \Zend_Db_Expr('RAND()');
        }
        $select->order($spec);
        return $this;
    }
    /**
     * Render SQL FOR UPDATE clause
     *
     * @param string $sql
     * @return string
     */
    public function for_update($sql)
    {
        return sprintf('%s FOR UPDATE', $sql);
    }
    /**
     * Prepare insert data
     *
     * @param mixed $row
     * @param array $bind
     * @return string
     */
    protected function _prepare_insert_data($row, &$bind)
    {
        $row = (array) $row;
        $line = [];
        foreach ($row as $value) {
            if ($value instanceof \Zend_Db_Expr) {
                $line[] = $value->__toString();
            } else {
                $line[] = '?';
                $bind[] = $value;
            }
        }
        $line = implode(', ', $line);
        return sprintf('(%s)', $line);
    }
    /**
     * Return insert sql query
     *
     * @param string $tableName
     * @param array $columns
     * @param array $values
     * @param null|int $strategy
     * @return string
     */
    protected function _get_insert_sql_query($table_name, array $columns, array $values, $strategy = null)
    {
        $table_name = $this->quote_identifier($table_name, true);
        $columns = array_map([$this, 'quoteIdentifier'], $columns);
        $columns = implode(',', $columns);
        $values = implode(', ', $values);
        $strategy = $strategy === self::INSERT_IGNORE ? 'IGNORE' : '';
        $insert_sql = sprintf('INSERT %s INTO %s (%s) VALUES %s', $strategy, $table_name, $columns, $values);
        return $insert_sql;
    }
    /**
     * Return replace sql query
     *
     * @param string $tableName
     * @param array $columns
     * @param array $values
     * @return string
     * @since 101.0.0
     */
    protected function _get_replace_sql_query($table_name, array $columns, array $values)
    {
        $table_name = $this->quote_identifier($table_name, true);
        $columns = array_map([$this, 'quoteIdentifier'], $columns);
        $columns = implode(',', $columns);
        $values = implode(', ', $values);
        $replace_sql = sprintf('REPLACE INTO %s (%s) VALUES %s', $table_name, $columns, $values);
        return $replace_sql;
    }
    /**
     * Return ddl type
     *
     * @param array $options
     * @return string
     */
    protected function _get_ddl_type($options)
    {
        $ddl_type = null;
        if (isset($options['TYPE'])) {
            $ddl_type = $options['TYPE'];
        } elseif (isset($options['COLUMN_TYPE'])) {
            $ddl_type = $options['COLUMN_TYPE'];
        }
        return $this->sanitize_column_data_type($ddl_type);
    }
    /**
     * Return DDL action
     *
     * @param string $action
     * @return string
     */
    protected function _get_ddl_action($action)
    {
        switch ($action) {
            case Adapter_Interface::FK_ACTION_CASCADE:
                return Table::ACTION_CASCADE;
            case Adapter_Interface::FK_ACTION_SET_NULL:
                return Table::ACTION_SET_NULL;
            case Adapter_Interface::FK_ACTION_RESTRICT:
                return Table::ACTION_RESTRICT;
            default:
                return Table::ACTION_NO_ACTION;
        }
    }
    /**
     * Prepare sql date condition
     *
     * @param array $condition
     * @param string $key
     * @return string
     */
    protected function _prepare_sql_date_condition($condition, $key)
    {
        if (empty($condition['date'])) {
            if (empty($condition['datetime'])) {
                $result = $condition[$key];
            } else {
                $result = $this->format_date($condition[$key]);
            }
        } else {
            $result = $this->format_date($condition[$key]);
        }
        return $result;
    }
    /**
     * Try to find installed primary key name, if not - formate new one.
     *
     * @param string $tableName Table name
     * @param string $schemaName OPTIONAL
     * @return string Primary Key name
     */
    public function get_primary_key_name($table_name, $schema_name = null)
    {
        $indexes = $this->get_index_list($table_name, $schema_name);
        if (isset($indexes['PRIMARY'])) {
            return $indexes['PRIMARY']['KEY_NAME'];
        } else {
            return 'PK_' . strtoupper($table_name);
        }
    }
    /**
     * Parse text size
     *
     * Returns max allowed size if value great it
     *
     * @param string|int $size
     * @return int
     */
    protected function _parse_text_size($size)
    {
        $size = trim($size);
        $last = strtolower(substr($size, -1));
        switch ($last) {
            case 'k':
                $size = (int) $size * 1024;
                break;
            case 'm':
                $size = (int) $size * 1024 * 1024;
                break;
            case 'g':
                $size = (int) $size * 1024 * 1024 * 1024;
                break;
        }
        if (empty($size)) {
            return Table::DEFAULT_TEXT_SIZE;
        }
        if ($size >= Table::MAX_TEXT_SIZE) {
            return Table::MAX_TEXT_SIZE;
        }
        return (int) $size;
    }
    /**
     * Converts fetched blob into raw binary PHP data.
     *
     * The MySQL drivers do it nice, no processing required.
     *
     * @param mixed $value
     * @return mixed
     */
    public function decode_varbinary($value)
    {
        return $value;
    }
    /**
     * Create trigger
     *
     * @param \Magento\Framework\DB\Ddl\Trigger $trigger
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Pdo
     */
    public function create_trigger(\Magento\Framework\DB\Ddl\Trigger $trigger)
    {
        if (!$trigger->get_statements()) {
            throw new \Zend_Db_Exception((string) new \Magento\Framework\Phrase('Trigger %1 has not statements available', [$trigger->get_name()]));
        }
        $statements = implode("\n", $trigger->get_statements());
        $sql = sprintf("CREATE TRIGGER %s %s %s ON %s FOR EACH ROW\nBEGIN\n%s\nEND", $trigger->get_name(), $trigger->get_time(), $trigger->get_event(), $trigger->get_table(), $statements);
        return $this->multi_query($sql);
    }
    /**
     * Drop trigger from database
     *
     * @param string $triggerName
     * @param string|null $schemaName
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function drop_trigger($trigger_name, $schema_name = null)
    {
        if (empty($trigger_name)) {
            throw new \InvalidArgumentException((string) new \Magento\Framework\Phrase('Trigger name is not defined'));
        }
        $trigger_name = ($schema_name ? $schema_name . '.' : '') . $trigger_name;
        $sql = 'DROP TRIGGER IF EXISTS ' . $this->quote_identifier($trigger_name);
        $this->query($sql);
        return true;
    }
    /**
     * Check if all transactions have been committed
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_transaction_level > 0) {
            while ($this->_transaction_level) {
                $this->roll_back();
            }
            $this->logger->log('Some transactions have not been committed or rolled back');
        }
    }
    /**
     * Retrieve tables list
     *
     * @param null|string $likeCondition
     * @return array
     */
    public function get_tables($like_condition = null)
    {
        $sql = $like_condition === null ? 'SHOW TABLES' : sprintf("SHOW TABLES LIKE '%s'", $like_condition);
        $result = $this->query($sql);
        $tables = [];
        while ($row = $result->fetch_column()) {
            $tables[] = $row;
        }
        return $tables;
    }
    /**
     * Returns auto increment field if exists
     *
     * @param string $tableName
     * @param string|null $schemaName
     * @return string|bool
     * @since 100.1.0
     */
    public function get_auto_increment_field($table_name, $schema_name = null)
    {
        $index_name = $this->get_primary_key_name($table_name, $schema_name);
        $indexes = $this->get_index_list($table_name);
        if ($index_name && isset($indexes[$index_name]) && count($indexes[$index_name]['COLUMNS_LIST']) == 1) {
            return current($indexes[$index_name]['COLUMNS_LIST']);
        }
        return false;
    }
    /**
     * Get schema Listener.
     *
     * Required to listen all DDL changes done by 3-rd party modules with old Install/UpgradeSchema scripts.
     *
     * @return SchemaListener
     * @since 102.0.0
     */
    public function get_schema_listener()
    {
        if ($this->schema_listener === null) {
            // phpcs:ignore Magento2.PHP.AutogeneratedClassNotInConstructor
            $this->schema_listener = \Magento\Framework\App\Object_Manager::get_instance()->create(Schema_Listener::class);
        }
        return $this->schema_listener;
    }
    /**
     * Closes the connection.
     *
     * @since 102.0.4
     */
    public function close_connection()
    {
        /**
         * _connect() function does not allow port parameter, so put the port back with the host
         */
        if (!empty($this->_config['port'])) {
            $this->_config['host'] = implode(':', [$this->_config['host'], $this->_config['port']]);
            unset($this->_config['port']);
        }
        parent::close_connection();
    }
    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
    /***
     * Set default collation & charset (e.g.utf8mb4_general_ci and utf8mb4) for tables
     *
     * @param string $columnType
     * @param string $definition
     * @param int $position
     * @return string
     */
    private function set_default_charset_and_collation($column_type, $definition, $position): string
    {
        $pattern = '/\b(' . implode('|', array_map('preg_quote', self::COLUMN_TYPE)) . ')\b/i';
        if (preg_match($pattern, $column_type) === 1) {
            $charset = $this->column_config->get_default_charset();
            $collate = $this->column_config->get_default_collation();
            $charsets = 'CHARACTER SET ' . $charset . ' COLLATE ' . $collate;
            $columns_attribute = explode(' ', trim($definition));
            array_splice($columns_attribute, $position, 0, $charsets);
            return implode(' ', $columns_attribute);
        }
        return $definition;
    }
}