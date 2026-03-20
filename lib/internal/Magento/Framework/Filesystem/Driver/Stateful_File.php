<?php

declare (strict_types=1);
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem\Driver_Interface;
/**
 * Origin filesystem driver. Filesystem driver that uses the local filesystem.
 *
 * Assumed that stat cache is cleanup by data modification methods
 *
 * @deprecated moved most of the functionality back to File
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Stateful_File implements Driver_Interface
{
    /**
     * @var string
     */
    protected $scheme = '';
    /**
     * @var File
     */
    private $driver_file;
    /**
     * StatefulFile constructor.
     * @param File $driverFile
     */
    public function __construct(?File $driver_file = null)
    {
        $this->driver_file = $driver_file ?? Object_Manager::get_instance()->create(File::class, ['stateful' => true]);
    }
    /**
     * Returns last warning message string
     *
     * @return string
     */
    protected function get_warning_message()
    {
        $warning = error_get_last();
        if ($warning && $warning['type'] == E_WARNING) {
            return 'Warning!' . $warning['message'];
        }
        return null;
    }
    /**
     * Is file or directory exist in file system
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function is_exists($path)
    {
        return $this->driver_file->is_exists($path);
    }
    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @throws FileSystemException
     */
    public function stat($path)
    {
        return $this->driver_file->stat($path);
    }
    /**
     * Check permissions for reading file or directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function is_readable($path)
    {
        return $this->driver_file->is_readable($path);
    }
    /**
     * Tells whether the filename is a regular file
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function is_file($path)
    {
        return $this->driver_file->is_file($path);
    }
    /**
     * Tells whether the filename is a regular directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function is_directory($path)
    {
        return $this->driver_file->is_directory($path);
    }
    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws FileSystemException
     */
    public function file_get_contents($path, $flag = null, $context = null)
    {
        return $this->driver_file->file_get_contents($path, $flag, $context);
    }
    /**
     * Check if given path is writable
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function is_writable($path)
    {
        return $this->driver_file->is_writable($path);
    }
    /**
     * Returns parent directory's path
     *
     * @param string $path
     * @return string
     */
    public function get_parent_directory($path)
    {
        return $this->driver_file->get_parent_directory($path);
    }
    /**
     * Create directory
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FileSystemException
     */
    public function create_directory($path, $permissions = 0777)
    {
        return $this->driver_file->create_directory($path, $permissions);
    }
    /**
     * Read directory
     *
     * @param string $path
     * @return string[]
     * @throws FileSystemException
     */
    public function read_directory($path)
    {
        return $this->driver_file->read_directory($path);
    }
    /**
     * Search paths by given regex
     *
     * @param string $pattern
     * @param string $path
     * @return string[]
     * @throws FileSystemException
     */
    public function search($pattern, $path)
    {
        return $this->driver_file->search($pattern, $path);
    }
    /**
     * Renames a file or directory
     *
     * @param string $oldPath
     * @param string $newPath
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FileSystemException
     */
    public function rename($old_path, $new_path, ?Driver_Interface $target_driver = null)
    {
        return $this->driver_file->rename($old_path, $new_path, $target_driver);
    }
    /**
     * Copy source into destination
     *
     * @param string $source
     * @param string $destination
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FileSystemException
     */
    public function copy($source, $destination, ?Driver_Interface $target_driver = null)
    {
        return $this->driver_file->copy($source, $destination, $target_driver);
    }
    /**
     * Create symlink on source and place it into destination
     *
     * @param string $source
     * @param string $destination
     * @param DriverInterface|null $targetDriver
     * @return bool
     * @throws FileSystemException
     */
    public function symlink($source, $destination, ?Driver_Interface $target_driver = null)
    {
        return $this->driver_file->symlink($source, $destination, $target_driver);
    }
    /**
     * Delete file
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function delete_file($path)
    {
        return $this->driver_file->delete_file($path);
    }
    /**
     * Recursive delete directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function delete_directory($path)
    {
        return $this->driver_file->delete_directory($path);
    }
    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FileSystemException
     */
    public function change_permissions($path, $permissions)
    {
        return $this->driver_file->change_permissions($path, $permissions);
    }
    /**
     * Recursively change permissions of given path
     *
     * @param string $path
     * @param int $dirPermissions
     * @param int $filePermissions
     * @return bool
     * @throws FileSystemException
     */
    public function change_permissions_recursively($path, $dir_permissions, $file_permissions)
    {
        return $this->driver_file->change_permissions_recursively($path, $dir_permissions, $file_permissions);
    }
    /**
     * Sets access and modification time of file.
     *
     * @param string $path
     * @param int|null $modificationTime
     * @return bool
     * @throws FileSystemException
     */
    public function touch($path, $modification_time = null)
    {
        return $this->driver_file->touch($path, $modification_time);
    }
    /**
     * Write contents to file in given path
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @return int The number of bytes that were written.
     * @throws FileSystemException
     */
    public function file_put_contents($path, $content, $mode = null)
    {
        return $this->driver_file->file_put_contents($path, $content, $mode);
    }
    /**
     * Open file
     *
     * @param string $path
     * @param string $mode
     * @return resource file
     * @throws FileSystemException
     */
    public function file_open($path, $mode)
    {
        return $this->driver_file->file_open($path, $mode);
    }
    /**
     * Reads the line content from file pointer (with specified number of bytes from the current position).
     *
     * @param resource $resource
     * @param int $length
     * @param string $ending [optional]
     * @return string
     * @throws FileSystemException
     */
    public function file_read_line($resource, $length, $ending = null)
    {
        return $this->driver_file->file_read_line($resource, $length, $ending);
    }
    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param resource $resource
     * @param int $length
     * @return string
     * @throws FileSystemException
     */
    public function file_read($resource, $length)
    {
        return $this->driver_file->file_read($resource, $length);
    }
    /**
     * Reads one CSV row from the file
     *
     * @param resource $resource
     * @param int $length [optional]
     * @param string $delimiter [optional]
     * @param string $enclosure [optional]
     * @param string $escape [optional]
     * @return array|bool|null
     * @throws FileSystemException
     */
    public function file_get_csv($resource, $length = 0, $delimiter = ',', $enclosure = '"', $escape = "\x00")
    {
        return $this->driver_file->file_get_csv($resource, $length, $delimiter, $enclosure, $escape);
    }
    /**
     * Returns position of read/write pointer
     *
     * @param resource $resource
     * @return int
     * @throws FileSystemException
     */
    public function file_tell($resource)
    {
        return $this->driver_file->file_tell($resource);
    }
    /**
     * Seeks to the specified offset
     *
     * @param resource $resource
     * @param int $offset
     * @param int $whence
     * @return int
     * @throws FileSystemException
     */
    public function file_seek($resource, $offset, $whence = SEEK_SET)
    {
        return $this->driver_file->file_seek($resource, $offset, $whence);
    }
    /**
     * Returns true if pointer at the end of file or in case of exception
     *
     * @param resource $resource
     * @return bool
     */
    public function end_of_file($resource)
    {
        return $this->driver_file->end_of_file($resource);
    }
    /**
     * Close file
     *
     * @param resource $resource
     * @return bool
     * @throws FileSystemException
     */
    public function file_close($resource)
    {
        return $this->driver_file->file_close($resource);
    }
    /**
     * Writes data to file
     *
     * @param resource $resource
     * @param string $data
     * @return int
     * @throws FileSystemException
     */
    public function file_write($resource, $data)
    {
        return $this->driver_file->file_write($resource, $data);
    }
    /**
     * Writes one CSV row to the file.
     *
     * @param resource $resource
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return int
     * @throws FileSystemException
     */
    public function file_put_csv($resource, array $data, $delimiter = ',', $enclosure = '"')
    {
        return $this->driver_file->file_put_csv($resource, $data, $delimiter, $enclosure);
    }
    /**
     * Flushes the output
     *
     * @param resource $resource
     * @return bool
     * @throws FileSystemException
     */
    public function file_flush($resource)
    {
        return $this->driver_file->file_flush($resource);
    }
    /**
     * Lock file in selected mode
     *
     * @param resource $resource
     * @param int $lockMode
     * @return bool
     * @throws FileSystemException
     */
    public function file_lock($resource, $lock_mode = LOCK_EX)
    {
        return $this->driver_file->file_lock($resource, $lock_mode);
    }
    /**
     * Unlock file
     *
     * @param resource $resource
     * @return bool
     * @throws FileSystemException
     */
    public function file_unlock($resource)
    {
        return $this->driver_file->file_unlock($resource);
    }
    /**
     * Returns an absolute path for the given one.
     *
     * @param string $basePath
     * @param string $path
     * @param string|null $scheme
     * @return string
     */
    public function get_absolute_path($base_path, $path, $scheme = null)
    {
        return $this->driver_file->get_absolute_path($base_path, $path, $scheme);
    }
    /**
     * Retrieves relative path
     *
     * @param string $basePath
     * @param string $path
     * @return string
     */
    public function get_relative_path($base_path, $path = null)
    {
        return $this->driver_file->get_relative_path($base_path, $path);
    }
    /**
     * Fixes path separator.
     *
     * Utility method.
     *
     * @param string $path
     * @return string
     */
    protected function fix_separator($path)
    {
        return $path !== null ? str_replace('\\', '/', $path) : '';
    }
    /**
     * Return path with scheme
     *
     * @param null|string $scheme
     * @return string
     */
    protected function get_scheme($scheme = null)
    {
        return $scheme ? $scheme . '://' : '';
    }
    /**
     * Read directory recursively
     *
     * @param string $path
     * @return string[]
     * @throws FileSystemException
     */
    public function read_directory_recursively($path = null)
    {
        return $this->driver_file->read_directory_recursively($path);
    }
    /**
     * Get real path
     *
     * @param string $path
     *
     * @return string|bool
     */
    public function get_real_path($path)
    {
        return $this->driver_file->get_real_path($path);
    }
    /**
     * Return correct path for link
     *
     * @param string $path
     * @return mixed
     */
    public function get_real_path_safety($path)
    {
        return $this->driver_file->get_real_path_safety($path);
    }
}