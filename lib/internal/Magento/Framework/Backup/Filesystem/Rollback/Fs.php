<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup\Filesystem\Rollback;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Archive;
use Magento\Framework\Archive\Gz;
use Magento\Framework\Archive\Helper\File;
use Magento\Framework\Archive\Helper\File\Gz as HelperGz;
use Magento\Framework\Archive\Tar;
use Magento\Framework\Backup\Exception\Cant_Load_Snapshot;
use Magento\Framework\Backup\Exception\Not_Enough_Permissions;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
/**
 * Rollback worker for rolling back via local filesystem
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Fs extends Abstract_Rollback
{
    /**
     * @var Helper
     */
    private $fs_helper;
    /**
     * Files rollback implementation via local filesystem
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
        $fs_helper = $this->get_fs_helper();
        $files_info = $fs_helper->get_info($this->_snapshot->get_root_dir(), Helper::INFO_WRITABLE, $this->_snapshot->get_ignore_paths());
        if (!$files_info['writable']) {
            if (!empty($files_info['writableMeta'])) {
                throw new Not_Enough_Permissions(new Phrase('You need write permissions for: %1', [implode(', ', $files_info['writableMeta'])]));
            }
            throw new Not_Enough_Permissions(new Phrase("The rollback can't be executed because not all files are writable."));
        }
        $archiver = new Archive();
        /**
         * we need these fake initializations because all magento's files in filesystem will be deleted and autoloader
         * won't be able to load classes that we need for unpacking
         */
        new Tar();
        new Gz();
        new File('');
        new Helper_Gz('');
        new Localized_Exception(new Phrase('dummy'));
        if (!$this->_snapshot->keep_source_file()) {
            $fs_helper->rm($this->_snapshot->get_root_dir(), $this->_snapshot->get_ignore_paths());
        }
        $archiver->unpack($snapshot_path, $this->_snapshot->get_root_dir());
        if ($this->_snapshot->keep_source_file() === false) {
            @unlink($snapshot_path);
        }
    }
    /**
     * Get file system helper instance
     *
     * @return Helper
     * @deprecated 101.0.0
     */
    private function get_fs_helper()
    {
        if (!$this->fs_helper) {
            $this->fs_helper = Object_Manager::get_instance()->get(Helper::class);
        }
        return $this->fs_helper;
    }
}