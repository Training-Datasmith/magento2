<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Archive\Gz;
use Magento\Framework\Backup\Archive\Tar;
use Magento\Framework\Backup\Exception\Not_Enough_Free_Space;
use Magento\Framework\Backup\Exception\Not_Enough_Permissions;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Backup\Filesystem\Rollback\Fs;
use Magento\Framework\Backup\Filesystem\Rollback\Ftp;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
/**
 * Class to work with filesystem backups
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Filesystem extends Abstract_Backup
{
    /**
     * Paths that ignored when creating or rolling back snapshot
     *
     * @var array
     */
    protected $_ignore_paths = [];
    /**
     * Whether use ftp account for rollback procedure
     *
     * @var bool
     */
    protected $_use_ftp = false;
    /**
     * Ftp host
     *
     * @var string
     */
    protected $_ftp_host;
    /**
     * Ftp username
     *
     * @var string
     */
    protected $_ftp_user;
    /**
     * Password to ftp account
     *
     * @var string
     */
    protected $_ftp_pass;
    /**
     * Ftp path to Magento installation
     *
     * @var string
     */
    protected $_ftp_path;
    /**
     * @var Ftp
     */
    protected $roll_back_ftp;
    /**
     * @var Fs
     */
    protected $roll_back_fs;
    /**
     * Implementation Rollback functionality for Filesystem
     *
     * @throws LocalizedException
     * @return bool
     */
    public function rollback()
    {
        $this->_last_operation_succeed = false;
        set_time_limit(0);
        ignore_user_abort(true);
        $rollback_worker = $this->_use_ftp ? $this->get_roll_back_ftp() : $this->get_roll_back_fs();
        $rollback_worker->run();
        $this->_last_operation_succeed = true;
        return $this->_last_operation_succeed;
    }
    /**
     * Implementation Create Backup functionality for Filesystem
     *
     * @throws LocalizedException
     * @return boolean
     */
    public function create()
    {
        set_time_limit(0);
        ignore_user_abort(true);
        $this->_last_operation_succeed = false;
        $this->_check_backups_dir();
        $fs_helper = new Helper();
        $files_info = $fs_helper->get_info($this->get_root_dir(), Helper::INFO_READABLE | Helper::INFO_SIZE, $this->get_ignore_paths());
        if (!$files_info['readable']) {
            throw new Not_Enough_Permissions(new Phrase('Not enough permissions to read files for backup'));
        }
        $this->validate_available_disc_space($this->get_backups_dir(), $files_info['size']);
        $tar_tmp_path = $this->_get_tar_tmp_path();
        $tar_packer = new Tar();
        $tar_packer->set_skip_files($this->get_ignore_paths())->pack($this->get_root_dir(), $tar_tmp_path, true);
        if (!is_file($tar_tmp_path) || filesize($tar_tmp_path) == 0) {
            throw new Localized_Exception(new Phrase('Failed to create backup'));
        }
        $backup_path = $this->get_backup_path();
        $gz_packer = new Gz();
        $gz_packer->pack($tar_tmp_path, $backup_path);
        if (!is_file($backup_path) || filesize($backup_path) == 0) {
            throw new Localized_Exception(new Phrase('Failed to create backup'));
        }
        @unlink($tar_tmp_path);
        $this->_last_operation_succeed = true;
        return $this->_last_operation_succeed;
    }
    /**
     * Validate if disk space is available for creating backup
     *
     * @param string $backupDir
     * @param int $size
     * @return void
     * @throws LocalizedException
     */
    public function validate_available_disc_space($backup_dir, $size)
    {
        $free_space = disk_free_space($backup_dir);
        $required_space = 2 * $size;
        if ($required_space > $free_space) {
            throw new Not_Enough_Free_Space(new Phrase('Warning: necessary space for backup is ' . ceil($required_space) / 1024 . 'MB, but your free disc space is ' . ceil($free_space) / 1024 . 'MB.'));
        }
    }
    /**
     * Force class to use ftp for rollback procedure
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $path
     * @return $this
     */
    public function set_use_ftp($host, $username, $password, $path)
    {
        $this->_use_ftp = true;
        $this->_ftp_host = $host;
        $this->_ftp_user = $username;
        $this->_ftp_pass = $password;
        $this->_ftp_path = $path;
        return $this;
    }
    /**
     * Get backup type
     *
     * @return string
     *
     * @see BackupInterface::getType()
     */
    public function get_type()
    {
        return 'filesystem';
    }
    /**
     * Add path that should be ignoring when creating or rolling back backup
     *
     * @param string|array $paths
     * @return $this
     */
    public function add_ignore_paths($paths)
    {
        if (is_string($paths)) {
            if (!in_array($paths, $this->_ignore_paths)) {
                $this->_ignore_paths[] = $paths;
            }
        } elseif (is_array($paths)) {
            foreach ($paths as $path) {
                $this->add_ignore_paths($path);
            }
        }
        return $this;
    }
    /**
     * Get paths that should be ignored while creating or rolling back backup procedure
     *
     * @return array
     */
    public function get_ignore_paths()
    {
        return $this->_ignore_paths;
    }
    /**
     * Set directory where backups saved and add it to ignore paths
     *
     * @param string $backupsDir
     * @return $this
     *
     * @see AbstractBackup::setBackupsDir()
     */
    public function set_backups_dir($backups_dir)
    {
        $backups_dir = rtrim($backups_dir, '/');
        parent::set_backups_dir($backups_dir);
        $this->add_ignore_paths($backups_dir);
        return $this;
    }
    /**
     * Getter for $_ftpPath variable
     *
     * @return string
     */
    public function get_ftp_path()
    {
        return $this->_ftp_path;
    }
    /**
     * Get ftp connection string
     *
     * @return string
     */
    public function get_ftp_connect_string()
    {
        return 'ftp://' . $this->_ftp_user . ':' . $this->_ftp_pass . '@' . $this->_ftp_host . $this->_ftp_path;
    }
    /**
     * Check backups directory existence and whether it's writeable
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _check_backups_dir()
    {
        $backups_dir = $this->get_backups_dir();
        if (!is_dir($backups_dir)) {
            $backups_dir_parent_directory = basename($backups_dir);
            if (!is_writeable($backups_dir_parent_directory)) {
                throw new Not_Enough_Permissions(new Phrase('Cant create backups directory'));
            }
            mkdir($backups_dir);
            chmod($backups_dir, 0755);
        }
        if (!is_writable($backups_dir)) {
            throw new Not_Enough_Permissions(new Phrase('Backups directory is not writeable'));
        }
    }
    /**
     * Generate tmp name for tarball
     *
     * @return string
     */
    protected function _get_tar_tmp_path()
    {
        $tmp_name = '~tmp-' . microtime(true) . '.tar';
        return $this->get_backups_dir() . '/' . $tmp_name;
    }
    /**
     * Get rollback FTP
     *
     * @return Ftp
     * @deprecated 101.0.0
     */
    protected function get_roll_back_ftp()
    {
        if (!$this->roll_back_ftp) {
            $this->roll_back_ftp = Object_Manager::get_instance()->create(Ftp::class, ['snapshotObject' => $this]);
        }
        return $this->roll_back_ftp;
    }
    /**
     * Get rollback FS
     *
     * @return Fs
     * @deprecated 101.0.0
     */
    protected function get_roll_back_fs()
    {
        if (!$this->roll_back_fs) {
            $this->roll_back_fs = Object_Manager::get_instance()->create(Fs::class, ['snapshotObject' => $this]);
        }
        return $this->roll_back_fs;
    }
}