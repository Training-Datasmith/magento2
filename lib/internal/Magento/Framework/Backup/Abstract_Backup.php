<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup;

use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
/**
 * Class to work with archives
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Backup implements Backup_Interface, Source_File_Interface
{
    /**
     * Backup name
     *
     * @var string
     */
    protected $_name;
    /**
     * Backup creation date
     *
     * @var int
     */
    protected $_time;
    /**
     * Backup file extension
     *
     * @var string
     */
    protected $_backup_extension;
    /**
     * @var object
     */
    protected $_resource_model;
    /**
     * Magento's root directory
     *
     * @var string
     */
    protected $_root_dir;
    /**
     * Path to directory where backups stored
     *
     * @var string
     */
    protected $_backups_dir;
    /**
     * Is last operation completed successfully
     *
     * @var bool
     */
    protected $_last_operation_succeed = false;
    /**
     * Last failed operation error message
     *
     * @var string
     */
    protected $_last_error_message;
    /**
     * Keep Source files in Backup
     *
     * @var boolean
     */
    private $keep_source_file;
    /**
     * Set Backup Extension
     *
     * @param string $backupExtension
     * @return $this
     */
    public function set_backup_extension($backup_extension)
    {
        $this->_backup_extension = $backup_extension;
        return $this;
    }
    /**
     * Get Backup Extension
     *
     * @return string
     */
    public function get_backup_extension()
    {
        return $this->_backup_extension;
    }
    /**
     * Set Resource Model
     *
     * @param object $resourceModel
     * @return $this
     */
    public function set_resource_model($resource_model)
    {
        $this->_resource_model = $resource_model;
        return $this;
    }
    /**
     * Get Resource Model
     *
     * @return object
     */
    public function get_resource_model()
    {
        return $this->_resource_model;
    }
    /**
     * Set Time
     *
     * @param int $time
     * @return $this
     */
    public function set_time($time)
    {
        $this->_time = $time;
        return $this;
    }
    /**
     * Get Time
     *
     * @return int
     */
    public function get_time()
    {
        return $this->_time;
    }
    /**
     * Set root directory of Magento installation
     *
     * @param string $rootDir
     * @throws LocalizedException
     * @return $this
     */
    public function set_root_dir($root_dir)
    {
        if (!is_dir($root_dir)) {
            throw new Localized_Exception(new Phrase('Bad root directory'));
        }
        $this->_root_dir = rtrim($root_dir, '/');
        return $this;
    }
    /**
     * Get Magento's root directory
     *
     * @return string
     */
    public function get_root_dir()
    {
        return $this->_root_dir;
    }
    /**
     * Set path to directory where backups stored
     *
     * @param string $backupsDir
     * @return $this
     */
    public function set_backups_dir($backups_dir)
    {
        $this->_backups_dir = $backups_dir !== null ? rtrim($backups_dir, '/') : '';
        return $this;
    }
    /**
     * Get path to directory where backups stored
     *
     * @return string
     */
    public function get_backups_dir()
    {
        return $this->_backups_dir;
    }
    /**
     * Get path to backup
     *
     * @return string
     */
    public function get_backup_path()
    {
        return $this->get_backups_dir() . '/' . $this->get_backup_filename();
    }
    /**
     * Get backup file name
     *
     * @return string
     */
    public function get_backup_filename()
    {
        $filename = $this->get_time() . '_' . $this->get_type();
        $name = $this->get_name();
        if (!empty($name)) {
            $filename .= '_' . $name;
        }
        $filename .= '.' . $this->get_backup_extension();
        return $filename;
    }
    /**
     * Check whether last operation completed successfully
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_is_success()
    {
        return $this->_last_operation_succeed;
    }
    /**
     * Get last error message
     *
     * @return string
     */
    public function get_error_message()
    {
        return $this->_last_error_message;
    }
    /**
     * Set error message
     *
     * @param string $errorMessage
     * @return void
     */
    public function set_error_message($error_message)
    {
        $this->_last_error_message = $error_message;
    }
    /**
     * Set backup name
     *
     * @param string $name
     * @param bool $applyFilter
     * @return $this
     */
    public function set_name($name, $apply_filter = true)
    {
        if ($apply_filter) {
            $name = $this->_filter_name($name);
        }
        $this->_name = $name;
        return $this;
    }
    /**
     * Get backup name
     *
     * @return string
     */
    public function get_name()
    {
        return $this->_name;
    }
    /**
     * Get backup display name
     *
     * @return string
     */
    public function get_display_name()
    {
        return $this->_name !== null ? str_replace('_', ' ', $this->_name) : '';
    }
    /**
     * Removes disallowed characters and replaces spaces with underscores
     *
     * @param string $name
     * @return string
     */
    protected function _filter_name($name)
    {
        $name = $name !== null ? trim(preg_replace('/[^\da-zA-Z ]/', '', $name)) : '';
        $name = preg_replace('/\s{2,}/', ' ', $name);
        return str_replace(' ', '_', $name);
    }
    /**
     * Check if keep files of backup
     *
     * @return bool
     * @since 102.0.0
     */
    public function keep_source_file()
    {
        return $this->keep_source_file;
    }
    /**
     * Set if keep files of backup
     *
     * @param bool $keepSourceFile
     * @return $this
     * @since 102.0.0
     */
    public function set_keep_source_file(bool $keep_source_file)
    {
        $this->keep_source_file = $keep_source_file;
        return $this;
    }
}