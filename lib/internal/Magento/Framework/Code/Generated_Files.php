<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem\Directory\Write_Factory;
use Magento\Framework\Filesystem\Directory\Write_Interface;
use Magento\Framework\Lock\Lock_Manager_Interface;
/**
 * Clean generated code, DI configuration and cache folders
 */
class Generated_Files
{
    /**
     * Regenerate flag file name
     */
    public const REGENERATE_FLAG = '/var/.regenerate';
    /**
     * Regenerate lock file name
     */
    public const REGENERATE_LOCK = self::REGENERATE_FLAG . '.lock';
    /**
     * Acquire regenerate lock timeout
     */
    public const REGENERATE_LOCK_TIMEOUT = 5;
    /**
     * @var DirectoryList
     */
    private $directory_list;
    /**
     * @var WriteInterface
     */
    private $write;
    /**
     * @var LockManagerInterface
     */
    private $lock_manager;
    /**
     * GeneratedFiles constructor.
     *
     * @param DirectoryList $directoryList
     * @param WriteFactory $writeFactory
     * @param LockManagerInterface $lockManager
     */
    public function __construct(Directory_List $directory_list, Write_Factory $write_factory, Lock_Manager_Interface $lock_manager)
    {
        $this->directory_list = $directory_list;
        $this->write = $write_factory->create(BP);
        $this->lock_manager = $lock_manager;
    }
    /**
     * Create flag for cleaning up generated content
     *
     * @return void
     */
    public function request_regeneration()
    {
        $this->write->touch(self::REGENERATE_FLAG);
    }
    /**
     * Clean generated code, generated metadata and cache directories
     *
     * @return void
     *
     * @deprecated 100.1.0
     * @see \Magento\Framework\Code\GeneratedFiles::cleanGeneratedFiles
     */
    public function regenerate()
    {
        $this->clean_generated_files();
    }
    /**
     * Clean generated code, generated metadata and cache directories
     *
     * @return void
     */
    public function clean_generated_files()
    {
        if ($this->is_clean_generated_files_allowed() && $this->acquire_lock()) {
            try {
                $this->write->delete(self::REGENERATE_FLAG);
                $this->delete_folder(Directory_List::GENERATED_CODE);
                $this->delete_folder(Directory_List::GENERATED_METADATA);
                $this->delete_folder(Directory_List::CACHE);
            } catch (File_System_Exception $exception) {
                // A filesystem error occurred, possible concurrency error while trying
                // to delete a generated folder being used by another process.
                // Request regeneration for the next and unlock
                $this->request_regeneration();
            } finally {
                $this->lock_manager->unlock(self::REGENERATE_LOCK);
            }
        }
    }
    /**
     * Clean generated files is allowed if requested and not locked
     *
     * @return bool
     */
    private function is_clean_generated_files_allowed(): bool
    {
        try {
            $is_allowed = $this->write->is_exist(self::REGENERATE_FLAG) && !$this->lock_manager->is_locked(self::REGENERATE_LOCK);
        } catch (File_System_Exception|RuntimeException $e) {
            // Possible filesystem problem
            $is_allowed = false;
        }
        return $is_allowed;
    }
    /**
     * Acquire lock for performing operations
     *
     * @return bool
     */
    private function acquire_lock(): bool
    {
        try {
            $lock_acquired = $this->lock_manager->lock(self::REGENERATE_LOCK, self::REGENERATE_LOCK_TIMEOUT);
        } catch (RuntimeException $exception) {
            // Lock not acquired due to possible filesystem problem
            $lock_acquired = false;
        }
        return $lock_acquired;
    }
    /**
     * Delete folder by path
     *
     * @param string $pathType
     * @return void
     */
    private function delete_folder(string $path_type): void
    {
        $relative_path = $this->write->get_relative_path($this->directory_list->get_path($path_type));
        if ($this->write->is_directory($relative_path)) {
            $this->write->delete($relative_path);
        }
    }
}