<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\DB\Adapter;

use Magento\Framework\App\Resource_Connection;
/**
 * Class GetDbVersion provides sql engine version requesting version variable
 *
 * Rather then depending on this class, please implement this logic in your extension
 */
class Sql_Version_Provider
{
    /**#@+
     * Database version specific templates
     */
    public const MYSQL_8_0_VERSION = '8.0.';
    public const MYSQL_5_7_VERSION = '5.7.';
    /**
     * @deprecated MARIA_DB_10_VERSION const
     * @see isMysqlGte8029(), isMariaDbEngine()
     */
    public const MARIA_DB_10_VERSION = '10.';
    public const MARIA_DB_10_4_VERSION = '10.4.';
    public const MARIA_DB_10_6_VERSION = '10.6.';
    public const MARIA_DB_10_11_VERSION = '10.11.';
    public const MYSQL_8_0_29_VERSION = '8.0.29';
    public const MARIA_DB_10_6_11_VERSION = '10.6.11';
    public const MARIA_DB_10_4_27_VERSION = '10.4.27';
    public const MYSQL_8_4_VERSION = '8.4.';
    public const MARIA_DB_11_4_VERSION = '11.4.';
    public const MARIA_DB = 'mariadb';
    /**#@-*/
    /**
     * Database version variable name
     */
    private const VERSION_VAR_NAME = 'version';
    /**
     * @var ResourceConnection
     */
    private $resource_connection;
    /**
     * @var string
     */
    private $version;
    /**
     * @var array
     */
    private $supported_version_patterns;
    /**
     * @param ResourceConnection $resourceConnection
     * @param array $supportedVersionPatterns
     */
    public function __construct(Resource_Connection $resource_connection, array $supported_version_patterns = [])
    {
        $this->resource_connection = $resource_connection;
        $this->supported_version_patterns = $supported_version_patterns;
    }
    /**
     * Provides SQL engine version (MariaDB, MySQL-8, MySQL-5.7)
     *
     * @param string $resource
     *
     * @return string
     * @throws ConnectionException
     */
    public function get_sql_version(string $resource = Resource_Connection::DEFAULT_CONNECTION): string
    {
        if (!$this->version) {
            $this->version = $this->get_version_string($resource);
        }
        return $this->version;
    }
    /**
     * Provides Sql Engine Version string
     *
     * @param string $resource
     *
     * @return string
     * @throws ConnectionException
     */
    private function get_version_string(string $resource): string
    {
        $pattern = sprintf('/(%s)/', implode('|', $this->supported_version_patterns));
        $sql_version_output = $this->fetch_sql_version($resource);
        preg_match($pattern, $sql_version_output, $match);
        if (empty($match)) {
            throw new Connection_Exception(sprintf('Current version of RDBMS is not supported. Used Version: %s. Supported versions: %s', $sql_version_output, implode(', ', array_keys($this->supported_version_patterns))));
        }
        return reset($match);
    }
    /**
     * Fetch version from sql engine
     *
     * @param string $resource
     *
     * @return string
     */
    private function fetch_sql_version(string $resource): string
    {
        $version_output = $this->resource_connection->get_connection($resource)->fetch_pairs(sprintf('SHOW variables LIKE "%s"', self::VERSION_VAR_NAME));
        return $version_output[self::VERSION_VAR_NAME];
    }
    /**
     * Check if MySQL version is greater than equal to 8.0.29
     *
     * @return bool
     * @throws ConnectionException
     */
    public function is_mysql_gte8029(): bool
    {
        $is_maria_db = $this->is_maria_db_engine();
        $sql_exact_version = $this->fetch_sql_version(Resource_Connection::DEFAULT_CONNECTION);
        if (!$is_maria_db && version_compare($sql_exact_version, '8.0.29', '>=')) {
            return true;
        }
        return false;
    }
    /**
     * Get MariaDB current version
     *
     * @return string
     * @throws ConnectionException
     */
    public function get_maria_db_suffix_key(): string
    {
        $sql_version = $this->get_sql_version();
        $default_suffix_key = Sql_Version_Provider::MARIA_DB_10_6_11_VERSION;
        $is_maria_db104 = str_contains($sql_version, Sql_Version_Provider::MARIA_DB_10_4_VERSION);
        $is_maria_db106 = str_contains($sql_version, Sql_Version_Provider::MARIA_DB_10_6_VERSION);
        $is_maria_db1011 = str_contains($sql_version, Sql_Version_Provider::MARIA_DB_10_11_VERSION);
        $is_maria_db114 = str_contains($sql_version, Sql_Version_Provider::MARIA_DB_11_4_VERSION);
        $sql_exact_version = $this->fetch_sql_version(Resource_Connection::DEFAULT_CONNECTION);
        if (version_compare($sql_exact_version, '10.4.27', '>=')) {
            if ($is_maria_db104) {
                return Sql_Version_Provider::MARIA_DB_10_4_27_VERSION;
            } elseif ($is_maria_db106) {
                return Sql_Version_Provider::MARIA_DB_10_6_11_VERSION;
            } elseif ($is_maria_db114) {
                return Sql_Version_Provider::MARIA_DB_10_6_11_VERSION;
            } elseif ($is_maria_db1011) {
                return Sql_Version_Provider::MARIA_DB_10_11_VERSION;
            }
        }
        return $default_suffix_key;
    }
    /**
     * Checks if MariaDB used as SQL engine
     *
     * @return bool
     * @throws ConnectionException
     */
    public function is_maria_db_engine(): bool
    {
        // check current version else send exception
        $this->get_sql_version();
        // check current db is Maria DB
        $sql_exact_version = $this->fetch_sql_version(Resource_Connection::DEFAULT_CONNECTION);
        $is_maria_db = str_contains(strtolower($sql_exact_version), Sql_Version_Provider::MARIA_DB);
        return $is_maria_db ? true : false;
    }
}