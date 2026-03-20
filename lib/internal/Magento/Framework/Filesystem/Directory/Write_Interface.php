<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Filesystem\Directory;

/**
 * Interface \Magento\Framework\Filesystem\Directory\WriteInterface
 * @api
 * @since 100.0.2
 */
interface Write_Interface extends Read_Interface
{
    /**
     * Create directory if it does not exists
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function create($path = null);
    /**
     * Delete given path
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function delete($path = null);
    /**
     * Rename a file
     *
     * @param string $path
     * @param string $newPath
     * @param WriteInterface $targetDirectory [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function rename_file($path, $new_path, ?Write_Interface $target_directory = null);
    /**
     * Copy a file
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface $targetDirectory [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function copy_file($path, $destination, ?Write_Interface $target_directory = null);
    /**
     * Creates symlink on a file or directory and places it to destination
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface $targetDirectory [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function create_symlink($path, $destination, ?Write_Interface $target_directory = null);
    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function change_permissions($path, $permissions);
    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $dirPermissions
     * @param int $filePermissions
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function change_permissions_recursively($path, $dir_permissions, $file_permissions);
    /**
     * Sets access and modification time of file.
     *
     * @param string $path
     * @param int $modificationTime [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function touch($path, $modification_time = null);
    /**
     * Check if given path is writable
     *
     * @param string $path [optional]
     * @return bool
     */
    public function is_writable($path = null);
    /**
     * Open file in given mode
     *
     * @param string $path
     * @param string $mode
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     */
    public function open_file($path, $mode = 'w');
    /**
     * Open file in given path
     *
     * @param string $path
     * @param string $content
     * @param string $mode [optional]
     * @return int The number of bytes that were written.
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function write_file($path, $content, $mode = null);
    /**
     * Get driver
     *
     * @return \Magento\Framework\Filesystem\DriverInterface
     */
    public function get_driver();
}