<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup\Filesystem\Rollback;

use Magento\Framework\Backup\Exception\Cant_Load_Snapshot;
use Magento\Framework\Backup\Exception\Ftp_Connection_Failed;
use Magento\Framework\Backup\Exception\Ftp_Validation_Failed;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
/**
 * Rollback worker for rolling back via ftp
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Ftp extends Abstract_Rollback
{
    /**
     * @var \Magento\Framework\System\Ftp
     */
    protected $_ftp_client;
    /**
     * Files rollback implementation via ftp
     *
     * @return void
     * @throws LocalizedException
     *
     * @see AbstractRollback::run()
     */
    public function run()
    {
        $snapshot_path = $this->_snapshot->get_backup_path();
        if (!is_file($snapshot_path) || !is_readable($snapshot_path)) {
            throw new Cant_Load_Snapshot(new Phrase('Can\'t load snapshot archive'));
        }
        $this->_init_ftp_client();
        $this->_validate_ftp();
        $tmp_dir = $this->_create_tmp_dir();
        $this->_unpack_snapshot($tmp_dir);
        $fs_helper = new Helper();
        $this->_cleanup_ftp();
        $this->_upload_backup_to_ftp($tmp_dir);
        if ($this->_snapshot->keep_source_file() === false) {
            $fs_helper->rm($tmp_dir, [], true);
            $this->_ftp_client->delete($snapshot_path);
        }
    }
    /**
     * Initialize ftp client and connect to ftp
     *
     * @return void
     * @throws FtpConnectionFailed
     */
    protected function _init_ftp_client()
    {
        try {
            $this->_ftp_client = new \Magento\Framework\System\Ftp();
            $this->_ftp_client->connect($this->_snapshot->get_ftp_connect_string());
        } catch (\Exception $e) {
            throw new Ftp_Connection_Failed(new Phrase($e->get_message()));
        }
    }
    /**
     * Perform ftp validation. Check whether ftp account provided points to current magento installation
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _validate_ftp()
    {
        $validation_filename = '~validation-' . microtime(true) . '.tmp';
        $validation_file_path = $this->_snapshot->get_backups_dir() . '/' . $validation_filename;
        $fh = @fopen($validation_file_path, 'w');
        @fclose($fh);
        if (!is_file($validation_file_path)) {
            throw new Localized_Exception(new Phrase('Unable to validate ftp account'));
        }
        $root_dir = $this->_snapshot->get_root_dir() ?? '';
        $ftp_path = $this->_snapshot->get_ftp_path() . '/' . str_replace($root_dir, '', $validation_file_path);
        $file_exists_on_ftp = $this->_ftp_client->file_exists($ftp_path);
        @unlink($validation_file_path);
        if (!$file_exists_on_ftp) {
            throw new Ftp_Validation_Failed(new Phrase('Failed to validate ftp account'));
        }
    }
    /**
     * Unpack snapshot
     *
     * @param string $tmpDir
     * @return void
     */
    protected function _unpack_snapshot($tmp_dir)
    {
        $snapshot_path = $this->_snapshot->get_backup_path();
        $archiver = new \Magento\Framework\Archive();
        $archiver->unpack($snapshot_path, $tmp_dir);
    }
    /**
     * Method to create tmp dir.
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _create_tmp_dir()
    {
        $tmp_dir = $this->_snapshot->get_backups_dir() . '/~tmp-' . microtime(true);
        $result = @mkdir($tmp_dir);
        if (false === $result) {
            throw new \Magento\Framework\Backup\Exception\Not_Enough_Permissions(new Phrase('Failed to create directory %1', [$tmp_dir]));
        }
        return $tmp_dir;
    }
    /**
     * Delete magento and all files from ftp
     *
     * @return void
     */
    protected function _cleanup_ftp()
    {
        $root_dir = $this->_snapshot->get_root_dir();
        $filesystem_iterator = new \Recursive_Iterator_Iterator(new \Recursive_Directory_Iterator($root_dir), \Recursive_Iterator_Iterator::CHILD_FIRST);
        $iterator = new \Magento\Framework\Backup\Filesystem\Iterator\Filter($filesystem_iterator, $this->_snapshot->get_ignore_paths());
        foreach ($iterator as $item) {
            // @phpstan-ignore-next-line
            $ftp_path = $this->_snapshot->get_ftp_path() . '/' . str_replace($root_dir ?? '', '', $item->__toString());
            $ftp_path = str_replace('\\', '/', $ftp_path);
            $this->_ftp_client->delete($ftp_path);
        }
    }
    /**
     * Perform files rollback
     *
     * @param string $tmpDir
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _upload_backup_to_ftp($tmp_dir)
    {
        $filesystem_iterator = new \Recursive_Iterator_Iterator(new \Recursive_Directory_Iterator($tmp_dir), \Recursive_Iterator_Iterator::SELF_FIRST);
        $iterator = new \Magento\Framework\Backup\Filesystem\Iterator\Filter($filesystem_iterator, $this->_snapshot->get_ignore_paths());
        foreach ($filesystem_iterator as $item) {
            // @phpstan-ignore-next-line
            $ftp_path = $this->_snapshot->get_ftp_path() . '/' . str_replace($tmp_dir ?? '', '', $item->__toString());
            $ftp_path = str_replace('\\', '/', $ftp_path);
            if ($item->is_link()) {
                continue;
            }
            if ($item->is_dir()) {
                $this->_ftp_client->mkdir_recursive($ftp_path);
            } else {
                $result = $this->_ftp_client->put($ftp_path, $item->__toString());
                if (false === $result) {
                    throw new \Magento\Framework\Backup\Exception\Not_Enough_Permissions(new Phrase('Failed to upload file %1 to ftp', [$item->__toString()]));
                }
            }
        }
    }
}