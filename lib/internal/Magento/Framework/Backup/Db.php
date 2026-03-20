<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup;

use Magento\Framework\Archive;
use Magento\Framework\Backup\Db\Backup_Factory;
use Magento\Framework\Backup\Filesystem\Iterator\File;
/**
 * Class to work with database backups
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
class Db extends Abstract_Backup
{
    /**
     * @var BackupFactory
     */
    protected $_backup_factory;
    /**
     * @param BackupFactory $backupFactory
     */
    public function __construct(Backup_Factory $backup_factory)
    {
        $this->_backup_factory = $backup_factory;
    }
    /**
     * Implements Rollback functionality for Db
     *
     * @return bool
     */
    public function rollback()
    {
        set_time_limit(0);
        ignore_user_abort(true);
        $this->_last_operation_succeed = false;
        $archive_manager = new Archive();
        $source = $archive_manager->unpack($this->get_backup_path(), $this->get_backups_dir());
        $file = new File($source);
        foreach ($file as $statement) {
            $this->get_resource_model()->run_command($statement);
        }
        if ($this->keep_source_file() === false) {
            @unlink($source);
        }
        $this->_last_operation_succeed = true;
        return true;
    }
    /**
     * Checks whether the line is last in sql command
     *
     * @param string $line
     * @return bool
     */
    protected function _is_line_last_in_command($line)
    {
        $clean_line = trim($line);
        $line_length = strlen($clean_line);
        $return_result = false;
        if ($line_length > 0) {
            $last_symbol_index = $line_length - 1;
            if ($clean_line[$last_symbol_index] == ';') {
                $return_result = true;
            }
        }
        return $return_result;
    }
    /**
     * Implements Create Backup functionality for Db
     *
     * @return bool
     */
    public function create()
    {
        set_time_limit(0);
        ignore_user_abort(true);
        $this->_last_operation_succeed = false;
        $backup = $this->_backup_factory->create_backup_model()->set_time($this->get_time())->set_type($this->get_type())->set_path($this->get_backups_dir())->set_name($this->get_name());
        $backup_db = $this->_backup_factory->create_backup_db_model();
        $backup_db->create_backup($backup);
        $this->_last_operation_succeed = true;
        return true;
    }
    /**
     * Get database size
     *
     * @return int
     */
    public function get_db_size()
    {
        $backup_db = $this->_backup_factory->create_backup_db_model();
        return $backup_db->get_db_backup_size();
    }
    /**
     * Get Backup Type
     *
     * @return string
     */
    public function get_type()
    {
        return 'db';
    }
}