<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Model\Resource_Model;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Stdlib\DateTime\DateTime;
/**
 * @api
 * @since 100.0.2
 */
class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * Tables foreign key data array
     * [tbl_name] = array(create foreign key strings)
     *
     * @var array
     */
    protected $_foreign_keys = [];
    /**
     * @var DateTime
     */
    protected $_core_date;
    /**
     * @param ResourceConnection $resource
     * @param string $modulePrefix
     * @param DateTime $coreDate
     */
    public function __construct(Resource_Connection $resource, $module_prefix, DateTime $core_date)
    {
        parent::__construct($resource, $module_prefix);
        $this->_core_date = $core_date;
    }
    /**
     * Retrieve SQL fragment for drop table
     *
     * @param string $tableName
     * @return string
     */
    public function get_table_drop_sql($table_name)
    {
        $quoted_table_name = $this->get_connection()->quote_identifier($table_name);
        return sprintf('DROP TABLE IF EXISTS %s;', $quoted_table_name);
    }
    /**
     * Retrieve foreign keys for table(s)
     *
     * @param string|null $tableName
     * @return string|bool
     */
    public function get_table_foreign_keys_sql($table_name = null)
    {
        $sql = false;
        if ($table_name === null) {
            $sql = '';
            foreach ($this->_foreign_keys as $table => $foreign_keys) {
                $sql .= $this->_build_foreign_keys_alter_table_sql($table, $foreign_keys);
            }
        } elseif (isset($this->_foreign_keys[$table_name])) {
            $foreign_keys = $this->_foreign_keys[$table_name];
            $sql = $this->_build_foreign_keys_alter_table_sql($table_name, $foreign_keys);
        }
        return $sql;
    }
    /**
     * Build sql that will add foreign keys to it
     *
     * @param string $tableName
     * @param array $foreignKeys
     * @return string
     */
    protected function _build_foreign_keys_alter_table_sql($table_name, $foreign_keys)
    {
        if (!is_array($foreign_keys) || empty($foreign_keys)) {
            return '';
        }
        return sprintf("ALTER TABLE %s\n  %s;\n", $this->get_connection()->quote_identifier($table_name), join(",\n  ", $foreign_keys));
    }
    /**
     * Get create script for table
     *
     * @param string $tableName
     * @param boolean $addDropIfExists
     * @return string
     */
    public function get_table_create_script($table_name, $add_drop_if_exists = false)
    {
        $script = '';
        $quoted_table_name = $this->get_connection()->quote_identifier($table_name);
        if ($add_drop_if_exists) {
            $script .= 'DROP TABLE IF EXISTS ' . $quoted_table_name . ";\n";
        }
        //TODO fix me
        $sql = 'SHOW CREATE TABLE ' . $quoted_table_name;
        $data = $this->get_connection()->fetch_row($sql);
        $script .= isset($data['Create Table']) ? $data['Create Table'] . ";\n" : '';
        return $script;
    }
    /**
     * Retrieve SQL fragment for create table
     *
     * @param string $tableName
     * @param bool $withForeignKeys
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function get_table_create_sql($table_name, $with_foreign_keys = false)
    {
        $connection = $this->get_connection();
        $quoted_table_name = $connection->quote_identifier($table_name);
        $query = 'SHOW CREATE TABLE ' . $quoted_table_name;
        $row = $connection->fetch_row($query);
        if (!$row || !isset($row['Table']) || !isset($row['Create Table'])) {
            return false;
        }
        $reg_exp = '/,\s+CONSTRAINT `([^`]*)` FOREIGN KEY \(`([^`]*)`\) ' . 'REFERENCES `([^`]*)` \(`([^`]*)`\)' . '( ON DELETE (RESTRICT|CASCADE|SET NULL|NO ACTION))?' . '( ON UPDATE (RESTRICT|CASCADE|SET NULL|NO ACTION))?/';
        $matches = [];
        preg_match_all($reg_exp, $row['Create Table'], $matches, PREG_SET_ORDER);
        if (is_array($matches)) {
            foreach ($matches as $match) {
                $this->_foreign_keys[$table_name][] = sprintf('ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s)%s%s', $connection->quote_identifier($match[1]), $connection->quote_identifier($match[2]), $connection->quote_identifier($match[3]), $connection->quote_identifier($match[4]), $match[5] ?? '', $match[7] ?? '');
            }
        }
        if ($with_foreign_keys) {
            $sql = $row['Create Table'];
        } else {
            $sql = preg_replace($reg_exp, '', $row['Create Table']);
        }
        return $sql . ';';
    }
    /**
     * Returns SQL header data, move from original resource model
     *
     * @return string
     */
    public function get_header()
    {
        $db_config = $this->get_connection()->get_config();
        $version_row = $this->get_connection()->fetch_row('SHOW VARIABLES LIKE \'version\'');
        $host_name = !empty($db_config['unix_socket']) ? $db_config['unix_socket'] : (!empty($db_config['host']) ? $db_config['host'] : 'localhost');
        $header = "-- Magento DB backup\n" . "--\n" . "-- Host: {$host_name}    Database: {$db_config['dbname']}\n" . "-- ------------------------------------------------------\n" . "-- Server version: {$version_row['Value']}\n\n" . "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n" . "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n" . "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n" . "/*!40101 SET NAMES utf8 */;\n" . "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n" . "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n" . "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n" . "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n";
        return $header;
    }
    /**
     * Returns SQL footer data, move from original resource model
     *
     * @return string
     */
    public function get_footer()
    {
        $footer = "\n/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n" . "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */; \n" . "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n" . "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n" . "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n" . "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n" . "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n" . "\n-- Dump completed on " . $this->_core_date->gmt_date() . ' GMT';
        return $footer;
    }
    /**
     * Retrieve before insert data SQL fragment
     *
     * @param string $tableName
     * @return string
     */
    public function get_table_data_before_sql($table_name)
    {
        $quoted_table_name = $this->get_connection()->quote_identifier($table_name);
        return "\n--\n" . "-- Dumping data for table {$quoted_table_name}\n" . "--\n\n" . "LOCK TABLES {$quoted_table_name} WRITE;\n" . "/*!40000 ALTER TABLE {$quoted_table_name} DISABLE KEYS */;\n";
    }
    /**
     * Retrieve after insert data SQL fragment
     *
     * @param string $tableName
     * @return string
     */
    public function get_table_data_after_sql($table_name)
    {
        $quoted_table_name = $this->get_connection()->quote_identifier($table_name);
        return "/*!40000 ALTER TABLE {$quoted_table_name} ENABLE KEYS */;\n" . "UNLOCK TABLES;\n";
    }
    /**
     * Return table part data SQL insert
     *
     * @param string $tableName
     * @param int $count
     * @param int $offset
     * @return string
     */
    public function get_part_insert_sql($table_name, $count = null, $offset = null)
    {
        $sql = null;
        $connection = $this->get_connection();
        $select = $connection->select()->from($table_name)->limit($count, $offset);
        $query = $connection->query($select);
        while (true == $row = $query->fetch()) {
            if ($sql === null) {
                $sql = sprintf('INSERT INTO %s VALUES ', $connection->quote_identifier($table_name));
            } else {
                $sql .= ',';
            }
            $sql .= $this->_quote_row($table_name, $row);
        }
        if ($sql !== null) {
            $sql .= ';' . "\n";
        }
        return $sql;
    }
    /**
     * Return table data SQL insert
     *
     * @param string $tableName
     * @return string
     */
    public function get_insert_sql($table_name)
    {
        return $this->get_part_insert_sql($table_name);
    }
    /**
     * Quote Table Row
     *
     * @param string $tableName
     * @param array $row
     * @return string
     */
    protected function _quote_row($table_name, array $row)
    {
        $connection = $this->get_connection();
        $describe = $connection->describe_table($table_name);
        $data_types = ['bigint', 'mediumint', 'smallint', 'tinyint'];
        $row_data = [];
        foreach ($row as $key => $data) {
            if ($data === null) {
                $value = 'NULL';
            } elseif (in_array(strtolower($describe[$key]['DATA_TYPE'] ?? ''), $data_types)) {
                $value = $data;
            } else {
                $value = $connection->quote_into('?', $data);
            }
            $row_data[] = $value;
        }
        return sprintf('(%s)', implode(',', $row_data));
    }
    /**
     * Prepare transaction isolation level for backup process
     *
     * @return void
     */
    public function prepare_transaction_isolation_level()
    {
        $this->get_connection()->query('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
    }
    /**
     * Restore transaction isolation level after backup
     *
     * @return void
     */
    public function restore_transaction_isolation_level()
    {
        $this->get_connection()->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    }
    /**
     * Get create script for triggers.
     *
     * @param string $tableName
     * @param boolean $addDropIfExists
     * @param boolean $stripDefiner
     * @return string
     * @since 100.2.3
     */
    public function get_table_triggers_sql($table_name, $add_drop_if_exists = false, $strip_definer = true)
    {
        $script = "--\n-- Triggers structure for table `{$table_name}`\n--\n";
        $triggers = $this->get_connection()->query('SHOW TRIGGERS LIKE \'' . $table_name . '\'')->fetch_all();
        if (!$triggers) {
            return '';
        }
        foreach ($triggers as $trigger) {
            if ($add_drop_if_exists) {
                $script .= 'DROP TRIGGER IF EXISTS ' . $trigger['Trigger'] . ";\n";
            }
            $script .= "delimiter ;;\n";
            $trigger_data = $this->get_connection()->query('SHOW CREATE TRIGGER ' . $trigger['Trigger'])->fetch();
            if ($strip_definer) {
                $cleaned_script = preg_replace('/DEFINER=[^\s]*/', '', $trigger_data['SQL Original Statement']);
                $script .= $cleaned_script . "\n";
            } else {
                $script .= $trigger_data['SQL Original Statement'] . "\n";
            }
            $script .= ";;\n";
            $script .= "delimiter ;\n";
        }
        return $script;
    }
}