<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Model\Resource_Model;

/**
 * Database backup resource model
 * @api
 * @since 100.0.2
 */
class Db
{
    /**
     * Database connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;
    /**
     * Tables foreign key data array
     * [tbl_name] = array(create foreign key strings)
     *
     * @var array
     */
    protected $_foreign_keys = [];
    /**
     * Backup resource helper
     *
     * @var \Magento\Backup\Model\ResourceModel\Helper
     */
    protected $_resource_helper;
    /**
     * Initialize Backup DB resource model
     *
     * @param \Magento\Backup\Model\ResourceModel\HelperFactory $resHelperFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(\Magento\Backup\Model\Resource_Model\Helper_Factory $res_helper_factory, \Magento\Framework\App\Resource_Connection $resource)
    {
        $this->_resource_helper = $res_helper_factory->create();
        $this->connection = $resource->get_connection('backup');
    }
    /**
     * Clear data
     *
     * @return void
     */
    public function clear()
    {
        $this->_foreign_keys = [];
    }
    /**
     * Retrieve table list
     *
     * @return array
     */
    public function get_tables()
    {
        return $this->connection->list_tables();
    }
    /**
     * Retrieve SQL fragment for drop table
     *
     * @param string $tableName
     * @return string
     */
    public function get_table_drop_sql($table_name)
    {
        return $this->_resource_helper->get_table_drop_sql($table_name);
    }
    /**
     * Retrieve SQL fragment for create table
     *
     * @param string $tableName
     * @param bool $withForeignKeys
     * @return string
     */
    public function get_table_create_sql($table_name, $with_foreign_keys = false)
    {
        return $this->_resource_helper->get_table_create_sql($table_name, $with_foreign_keys = false);
    }
    /**
     * Retrieve foreign keys for table(s)
     *
     * @param string|null $tableName
     * @return string
     */
    public function get_table_foreign_keys_sql($table_name = null)
    {
        $fk_script = '';
        if (!$table_name) {
            $tables = $this->get_tables();
            foreach ($tables as $table) {
                $table_fk_script = $this->_resource_helper->get_table_foreign_keys_sql($table);
                if (!empty($table_fk_script)) {
                    $fk_script .= "\n" . $table_fk_script;
                }
            }
        } else {
            $fk_script = $this->get_table_foreign_keys_sql($table_name);
        }
        return $fk_script;
    }
    /**
     * Return triggers for table(s).
     *
     * @param string|null $tableName
     * @param bool $addDropIfExists
     * @return string
     * @since 100.2.3
     */
    public function get_table_triggers_sql($table_name = null, $add_drop_if_exists = true)
    {
        $trigger_script = '';
        if (!$table_name) {
            $tables = $this->get_tables();
            foreach ($tables as $table) {
                $table_trigger_script = $this->_resource_helper->get_table_triggers_sql($table, $add_drop_if_exists);
                if (!empty($table_trigger_script)) {
                    $trigger_script .= "\n" . $table_trigger_script;
                }
            }
        } else {
            $trigger_script = $this->get_table_triggers_sql($table_name, $add_drop_if_exists);
        }
        return $trigger_script;
    }
    /**
     * Retrieve table status
     *
     * @param string $tableName
     * @return \Magento\Framework\DataObject|bool
     */
    public function get_table_status($table_name)
    {
        $row = $this->connection->show_table_status($table_name);
        if ($row) {
            $status_object = new \Magento\Framework\Data_Object();
            foreach ($row as $field => $value) {
                $status_object->set_data(strtolower($field), $value);
            }
            $cnt_row = $this->connection->fetch_row($this->connection->select()->from($table_name, 'COUNT(1) as rows'));
            $status_object->set_rows($cnt_row['rows']);
            return $status_object;
        }
        return false;
    }
    /**
     * Retrieve table partial data SQL insert
     *
     * @param string $tableName
     * @param null|int $count
     * @param null|int $offset
     * @return string
     */
    public function get_table_data_sql($table_name, $count = null, $offset = null)
    {
        return $this->_resource_helper->get_part_insert_sql($table_name, $count, $offset);
    }
    /**
     * Enter description here...
     *
     * @param string|array|\Zend_Db_Expr $tableName
     * @param bool $addDropIfExists
     * @return string
     */
    public function get_table_create_script($table_name, $add_drop_if_exists = false)
    {
        return $this->_resource_helper->get_table_create_script($table_name, $add_drop_if_exists);
    }
    /**
     * Retrieve table header comment
     *
     * @param string $tableName
     * @return string
     */
    public function get_table_header($table_name)
    {
        $quoted_table_name = $this->connection->quote_identifier($table_name);
        return "\n--\n" . "-- Table structure for table {$quoted_table_name}\n" . "--\n\n";
    }
    /**
     * Return table data dump
     *
     * @param string $tableName
     * @param bool $step
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_table_data_dump($table_name, $step = false)
    {
        return $this->get_table_data_sql($table_name);
    }
    /**
     * Returns SQL header data
     *
     * @return string
     */
    public function get_header()
    {
        return $this->_resource_helper->get_header();
    }
    /**
     * Returns SQL footer data
     *
     * @return string
     */
    public function get_footer()
    {
        return $this->_resource_helper->get_footer();
    }
    /**
     * Retrieve before insert data SQL fragment
     *
     * @param string $tableName
     * @return string
     */
    public function get_table_data_before_sql($table_name)
    {
        return $this->_resource_helper->get_table_data_before_sql($table_name);
    }
    /**
     * Retrieve after insert data SQL fragment
     *
     * @param string $tableName
     * @return string
     */
    public function get_table_data_after_sql($table_name)
    {
        return $this->_resource_helper->get_table_data_after_sql($table_name);
    }
    /**
     * Start transaction mode
     *
     * @return $this
     */
    public function begin_transaction()
    {
        $this->_resource_helper->prepare_transaction_isolation_level();
        $this->connection->begin_transaction();
        return $this;
    }
    /**
     * Commit transaction
     *
     * @return $this
     */
    public function commit_transaction()
    {
        $this->connection->commit();
        $this->_resource_helper->restore_transaction_isolation_level();
        return $this;
    }
    /**
     * Rollback transaction
     *
     * @return $this
     */
    public function roll_back_transaction()
    {
        $this->connection->roll_back();
        $this->_resource_helper->restore_transaction_isolation_level();
        return $this;
    }
    /**
     * Run sql code
     *
     * @param string $command
     * @return $this
     */
    public function run_command($command)
    {
        $this->connection->multi_query($command);
        return $this;
    }
}