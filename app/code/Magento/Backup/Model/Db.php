<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Model;

use Magento\Backup\Helper\Data as Helper;
use Magento\Backup\Model\Resource_Model\Table\Get_List_Tables;
use Magento\Backup\Model\Resource_Model\View\Create_Views_Backup;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\RuntimeException;
/**
 * Database backup model
 *
 * @api
 * @since 100.0.2
 * @deprecated 100.2.6 Backup module is to be removed.
 */
class Db implements \Magento\Framework\Backup\Db\Backup_Db_Interface
{
    /**
     * Buffer length for multi rows
     * default 100 Kb
     */
    public const BUFFER_LENGTH = 102400;
    /**
     * Backup resource model
     *
     * @var \Magento\Backup\Model\ResourceModel\Db
     */
    protected $_resource_db = null;
    /**
     * Core resource model
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource = null;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var GetListTables
     */
    private $get_list_tables;
    /**
     * @var CreateViewsBackup
     */
    private $get_views_backup;
    /**
     * Db constructor.
     * @param ResourceModel\Db $resourceDb
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Helper|null $helper
     * @param GetListTables|null $getListTables
     * @param CreateViewsBackup|null $getViewsBackup
     */
    public function __construct(Resource_Model\Db $resource_db, \Magento\Framework\App\Resource_Connection $resource, ?Helper $helper = null, ?Get_List_Tables $get_list_tables = null, ?Create_Views_Backup $get_views_backup = null)
    {
        $this->_resource_db = $resource_db;
        $this->_resource = $resource;
        $this->helper = $helper ?? Object_Manager::get_instance()->get(Helper::class);
        $this->get_list_tables = $get_list_tables ?? Object_Manager::get_instance()->get(Get_List_Tables::class);
        $this->get_views_backup = $get_views_backup ?? Object_Manager::get_instance()->get(Create_Views_Backup::class);
    }
    /**
     * List of tables which data should not be backed up
     *
     * @var array
     */
    protected $_ignore_data_tables_list = ['importexport/importdata'];
    /**
     * Retrieve resource model
     *
     * @return \Magento\Backup\Model\ResourceModel\Db
     */
    public function get_resource()
    {
        return $this->_resource_db;
    }
    /**
     * Tables list.
     *
     * @return array
     */
    public function get_tables()
    {
        return $this->get_resource()->get_tables();
    }
    /**
     * Command to recreate given table.
     *
     * @param string $tableName
     * @param bool $addDropIfExists
     * @return string
     */
    public function get_table_create_script($table_name, $add_drop_if_exists = false)
    {
        return $this->get_resource()->get_table_create_script($table_name, $add_drop_if_exists);
    }
    /**
     * Generate table's data dump.
     *
     * @param string $tableName
     * @return string
     */
    public function get_table_data_dump($table_name)
    {
        return $this->get_resource()->get_table_data_dump($table_name);
    }
    /**
     * Header for dumps.
     *
     * @return string
     */
    public function get_header()
    {
        return $this->get_resource()->get_header();
    }
    /**
     * Footer for dumps.
     *
     * @return string
     */
    public function get_footer()
    {
        return $this->get_resource()->get_footer();
    }
    /**
     * Get backup SQL.
     *
     * @return string
     */
    public function render_sql()
    {
        ini_set('max_execution_time', 0);
        $sql = $this->get_header();
        $tables = $this->get_tables();
        foreach ($tables as $table_name) {
            $sql .= $this->get_table_create_script($table_name, true);
            $sql .= $this->get_table_data_dump($table_name);
        }
        $sql .= $this->get_footer();
        return $sql;
    }
    /**
     * @inheritDoc
     */
    public function create_backup(\Magento\Framework\Backup\Db\Backup_Interface $backup)
    {
        if (!$this->helper->is_enabled()) {
            throw new RuntimeException(__('Backup functionality is disabled'));
        }
        $backup->open(true);
        $this->get_resource()->begin_transaction();
        $tables = $this->get_list_tables->execute();
        $backup->write($this->get_resource()->get_header());
        $ignore_data_tables_list = $this->get_ignore_data_tables_list();
        foreach ($tables as $table) {
            $backup->write($this->get_resource()->get_table_header($table) . $this->get_resource()->get_table_drop_sql($table) . "\n");
            $backup->write($this->get_resource()->get_table_create_sql($table, false) . "\n");
            $table_status = $this->get_resource()->get_table_status($table);
            if ($table_status->get_rows() && !in_array($table, $ignore_data_tables_list)) {
                $backup->write($this->get_resource()->get_table_data_before_sql($table));
                if ($table_status->get_data_length() > self::BUFFER_LENGTH) {
                    if ($table_status->get_avg_row_length() < self::BUFFER_LENGTH) {
                        $limit = floor(self::BUFFER_LENGTH / max($table_status->get_avg_row_length(), 1));
                        $multi_rows_length = ceil($table_status->get_rows() / $limit);
                    } else {
                        $limit = 1;
                        $multi_rows_length = $table_status->get_rows();
                    }
                } else {
                    $limit = $table_status->get_rows();
                    $multi_rows_length = 1;
                }
                for ($i = 0; $i < $multi_rows_length; $i++) {
                    $backup->write($this->get_resource()->get_table_data_sql($table, $limit, $i * $limit));
                }
                $backup->write($this->get_resource()->get_table_data_after_sql($table));
            }
        }
        $this->get_views_backup->execute($backup);
        $backup->write($this->get_resource()->get_table_foreign_keys_sql());
        $backup->write($this->get_resource()->get_table_triggers_sql());
        $backup->write($this->get_resource()->get_footer());
        $this->get_resource()->commit_transaction();
        $backup->close();
    }
    /**
     * Get database backup size
     *
     * @return int
     */
    public function get_db_backup_size()
    {
        $tables = $this->get_resource()->get_tables();
        $ignore_data_tables_list = $this->get_ignore_data_tables_list();
        $size = 0;
        foreach ($tables as $table) {
            $table_status = $this->get_resource()->get_table_status($table);
            if ($table_status->get_rows() && !in_array($table, $ignore_data_tables_list)) {
                $size += $table_status->get_data_length() + $table_status->get_index_length();
            }
        }
        return $size;
    }
    /**
     * Returns the list of tables which data should not be backed up
     *
     * @return string[]
     */
    public function get_ignore_data_tables_list()
    {
        $result = [];
        foreach ($this->_ignore_data_tables_list as $table) {
            $result[] = $this->_resource->get_table_name($table);
        }
        return $result;
    }
}