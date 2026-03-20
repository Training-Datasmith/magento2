<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Class to work with full filesystem and database backups
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Backup;

use Exception;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem as AppFilesystem;
class Snapshot extends Filesystem
{
    /**
     * Database backup manager
     *
     * @var Db
     */
    protected $_db_backup_manager;
    /**
     * Filesystem facade
     *
     * @var AppFilesystem
     */
    protected $_filesystem;
    /**
     * @var Factory
     */
    protected $_backup_factory;
    /**
     * @param AppFilesystem $filesystem
     * @param Factory $backupFactory
     */
    public function __construct(App_Filesystem $filesystem, Factory $backup_factory)
    {
        $this->_filesystem = $filesystem;
        $this->_backup_factory = $backup_factory;
    }
    /**
     * Implementation Rollback functionality for Snapshot
     *
     * @throws Exception
     * @return bool
     */
    public function rollback()
    {
        $result = parent::rollback();
        $this->_last_operation_succeed = false;
        try {
            $this->_get_db_backup_manager()->rollback();
        } catch (Exception $e) {
            $this->_remove_db_backup();
            throw $e;
        }
        $this->_remove_db_backup();
        $this->_last_operation_succeed = true;
        return $result;
    }
    /**
     * Implementation Create Backup functionality for Snapshot
     *
     * @throws Exception
     * @return bool
     */
    public function create()
    {
        $this->_get_db_backup_manager()->create();
        try {
            $result = parent::create();
        } catch (Exception $e) {
            $this->_remove_db_backup();
            throw $e;
        }
        $this->_last_operation_succeed = false;
        $this->_remove_db_backup();
        $this->_last_operation_succeed = true;
        return $result;
    }
    /**
     * Overlap getType
     *
     * @return string
     * @see BackupInterface::getType()
     */
    public function get_type()
    {
        return 'snapshot';
    }
    /**
     * Create Db Instance
     *
     * @return BackupInterface
     */
    protected function _create_db_backup_instance()
    {
        return $this->_backup_factory->create(Factory::TYPE_DB)->set_backup_extension('sql')->set_time($this->get_time())->set_backups_dir($this->_filesystem->get_directory_write(Directory_List::VAR_DIR)->get_absolute_path())->set_resource_model($this->get_resource_model());
    }
    /**
     * Get database backup manager
     *
     * @return Db
     */
    protected function _get_db_backup_manager()
    {
        if ($this->_db_backup_manager === null) {
            $this->_db_backup_manager = $this->_create_db_backup_instance();
        }
        return $this->_db_backup_manager;
    }
    /**
     * Set Db backup manager
     *
     * @param AbstractBackup $manager
     * @return $this
     */
    public function set_db_backup_manager(Abstract_Backup $manager)
    {
        $this->_db_backup_manager = $manager;
        return $this;
    }
    /**
     * Get Db Backup Filename
     *
     * @return string
     */
    public function get_db_backup_filename()
    {
        return $this->_get_db_backup_manager()->get_backup_filename();
    }
    /**
     * Remove Db backup after added it to the snapshot
     *
     * @return $this
     */
    protected function _remove_db_backup()
    {
        @unlink($this->_get_db_backup_manager()->get_backup_path());
        return $this;
    }
}