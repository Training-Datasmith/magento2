<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem\Driver_Interface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Phrase;
/**
 * Filesystem driver that uses the local filesystem.
 *
 * Assumed that stat cache is cleanup before test filesystem
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class File implements Driver_Interface
{
    /**
     * @var string
     */
    protected $scheme = '';
    /**
     * Flag for checking whether or not to be the behavior of statefulFile
     * @var bool
     */
    private $stateful;
    /**
     * File constructor.
     * @param bool $stateful
     */
    public function __construct(bool $stateful = false)
    {
        $this->stateful = $stateful;
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
        $filename = $this->get_scheme() . $path;
        if (!$this->stateful) {
            clearstatcache(false, $filename);
        }
        $result = @file_exists($filename);
        if ($result === null) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        $filename = $this->get_scheme() . $path;
        if (!$this->stateful) {
            clearstatcache(false, $filename);
        }
        $result = @stat($filename);
        if (!$result) {
            throw new File_System_Exception(new Phrase('Cannot gather stats! %1', [$this->get_warning_message()]));
        }
        return $result;
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
        $filename = $this->get_scheme() . $path;
        if (!$this->stateful) {
            clearstatcache(false, $filename);
        }
        $result = @is_readable($filename);
        if ($result === null) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        $filename = $this->get_scheme() . $path;
        if (!$this->stateful) {
            clearstatcache(false, $filename);
        }
        $result = @is_file($filename);
        if ($result === null) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        $filename = $this->get_scheme() . $path;
        if (!$this->stateful) {
            clearstatcache(false, $filename);
        }
        $result = @is_dir($filename);
        if ($result === null) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        $filename = $this->get_scheme() . $path;
        if (!$this->stateful) {
            clearstatcache(false, $filename);
        }
        $flag = $flag ?? false;
        $result = @file_get_contents($filename, $flag, $context);
        if (false === $result) {
            throw new File_System_Exception(new Phrase('The contents from the "%1" file can\'t be read. %2', [$path, $this->get_warning_message()]));
        }
        return $result;
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
        $filename = $this->get_scheme() . $path;
        if (!$this->stateful) {
            clearstatcache(false, $filename);
        }
        $result = @is_writable($filename);
        if ($result === null) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" execution.', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * Returns parent directory's path
     *
     * @param string $path
     * @return string
     */
    public function get_parent_directory($path)
    {
        return dirname($this->get_scheme() . $path);
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
        if ($this->stateful) {
            clearstatcache(true, $path);
        }
        return $this->mkdir_recursive($path, $permissions);
    }
    /**
     * Create a directory recursively taking into account race conditions
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FileSystemException
     */
    private function mkdir_recursive($path, $permissions = 0777)
    {
        $path = $this->get_scheme() . $path;
        if (is_dir($path)) {
            return true;
        }
        $parent_dir = dirname($path);
        while (!is_dir($parent_dir)) {
            $this->mkdir_recursive($parent_dir, $permissions);
        }
        $result = @mkdir($path, $permissions);
        if ($this->stateful) {
            clearstatcache(true, $path);
        }
        if (!$result) {
            if (is_dir($path)) {
                $result = true;
            } else {
                throw new File_System_Exception(new Phrase('Directory "%1" cannot be created %2', [$path, $this->get_warning_message()]));
            }
        }
        return $result;
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
        try {
            $flags = \Filesystem_Iterator::SKIP_DOTS | \Filesystem_Iterator::UNIX_PATHS | \Recursive_Directory_Iterator::FOLLOW_SYMLINKS;
            $iterator = new \Filesystem_Iterator($path, $flags);
            $result = [];
            /** @var \FilesystemIterator $file */
            foreach ($iterator as $file) {
                $result[] = $file->get_pathname();
            }
            sort($result);
            return $result;
        } catch (\Exception $e) {
            throw new File_System_Exception(new Phrase($e->get_message()), $e);
        }
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
        if (!$this->stateful) {
            clearstatcache();
        }
        $glob_pattern = rtrim((string) $path, '/') . '/' . ltrim((string) $pattern, '/');
        $result = Glob::glob($glob_pattern, Glob::GLOB_BRACE);
        return is_array($result) ? $result : [];
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
        $result = false;
        $target_driver = $target_driver ?: $this;
        if (get_class($target_driver) === get_class($this)) {
            $result = @rename($this->get_scheme() . $old_path, $new_path);
            if ($this->stateful) {
                clearstatcache(true, $this->get_scheme() . $old_path);
                clearstatcache(true, $new_path);
            }
            $this->change_permissions($new_path, 0777 & ~umask());
        } else {
            $content = $this->file_get_contents($old_path);
            if (false !== $target_driver->file_put_contents($new_path, $content)) {
                $result = $this->is_file($old_path) ? $this->delete_file($old_path) : true;
            }
        }
        if (!$result) {
            throw new File_System_Exception(new Phrase('The path "%1" cannot be renamed into "%2" %3', [$old_path, $new_path, $this->get_warning_message()]));
        }
        return $result;
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
        $target_driver = $target_driver ?: $this;
        if (get_class($target_driver) === get_class($this)) {
            $result = @copy($this->get_scheme() . $source, $destination);
            if ($this->stateful) {
                clearstatcache(true, $destination);
            }
        } else {
            $content = $this->file_get_contents($source);
            $result = $target_driver->file_put_contents($destination, $content);
        }
        if (!$result) {
            throw new File_System_Exception(new Phrase('The file or directory "%1" cannot be copied to "%2" %3', [$source, $destination, $this->get_warning_message()]));
        }
        return $result;
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
        $result = false;
        if ($target_driver === null || get_class($target_driver) == get_class($this)) {
            $result = @symlink($this->get_scheme() . $source, $destination);
            if ($this->stateful) {
                clearstatcache(true, $destination);
            }
        }
        if (!$result) {
            throw new File_System_Exception(new Phrase('A symlink for "%1" can\'t be created and placed to "%2". %3', [$source, $destination, $this->get_warning_message()]));
        }
        return $result;
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
        @unlink($this->get_scheme() . $path);
        if ($this->stateful) {
            clearstatcache(true, $this->get_scheme() . $path);
        }
        if ($this->is_file($path)) {
            throw new File_System_Exception(new Phrase('The "%1" file can\'t be deleted. %2', [$path, $this->get_warning_message()]));
        }
        return true;
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
        $exception_messages = [];
        $flags = \Filesystem_Iterator::SKIP_DOTS | \Filesystem_Iterator::UNIX_PATHS;
        $iterator = new \Filesystem_Iterator($path, $flags);
        /** @var \FilesystemIterator $entity */
        foreach ($iterator as $entity) {
            try {
                if ($entity->is_dir()) {
                    $this->delete_directory($entity->get_pathname());
                } else {
                    $this->delete_file($entity->get_pathname());
                }
            } catch (File_System_Exception $exception) {
                $exception_messages[] = $exception->get_message();
            }
        }
        if (!empty($exception_messages)) {
            throw new File_System_Exception(new Phrase(\implode(' ', $exception_messages)));
        }
        $full_path = $this->get_scheme() . $path;
        if (is_link($full_path)) {
            $result = @unlink($full_path);
        } else {
            $result = @rmdir($full_path);
        }
        if ($this->stateful) {
            clearstatcache(true, $full_path);
        }
        if (!$result) {
            throw new File_System_Exception(new Phrase('The directory "%1" cannot be deleted %2', [$path, $this->get_warning_message()]));
        }
        return $result;
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
        $result = @chmod($this->get_scheme() . $path, $permissions);
        if ($this->stateful) {
            clearstatcache(false, $this->get_scheme() . $path);
        }
        if (!$result) {
            throw new File_System_Exception(new Phrase('The permissions can\'t be changed for the "%1" path. %2.', [$path, $this->get_warning_message()]));
        }
        return $result;
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
        $result = true;
        if ($this->is_file($path)) {
            $result = @chmod($path, $file_permissions);
        } else {
            $result = @chmod($path, $dir_permissions);
        }
        if ($this->stateful) {
            clearstatcache(false, $this->get_scheme() . $path);
        }
        if (!$result) {
            throw new File_System_Exception(new Phrase('The permissions can\'t be changed for the "%1" path. %2.', [$path, $this->get_warning_message()]));
        }
        $flags = \Filesystem_Iterator::SKIP_DOTS | \Filesystem_Iterator::UNIX_PATHS;
        $iterator = new \Recursive_Iterator_Iterator(new \Recursive_Directory_Iterator($path, $flags), \Recursive_Iterator_Iterator::CHILD_FIRST);
        /** @var \FilesystemIterator $entity */
        foreach ($iterator as $entity) {
            if ($entity->is_dir()) {
                $result = @chmod($entity->get_pathname(), $dir_permissions);
            } else {
                $result = @chmod($entity->get_pathname(), $file_permissions);
            }
            if (!$result) {
                throw new File_System_Exception(new Phrase('The permissions can\'t be changed for the "%1" path. %2.', [$path, $this->get_warning_message()]));
            }
        }
        return $result;
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
        if (!$modification_time) {
            $result = @touch($this->get_scheme() . $path);
        } else {
            $result = @touch($this->get_scheme() . $path, $modification_time);
        }
        if ($this->stateful) {
            clearstatcache(true, $this->get_scheme() . $path);
        }
        if (!$result) {
            throw new File_System_Exception(new Phrase('The "%1" file or directory can\'t be touched. %2', [$path, $this->get_warning_message()]));
        }
        return $result;
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
        $mode = $mode ?? 0;
        $result = @file_put_contents($this->get_scheme() . $path, $content, $mode);
        if ($this->stateful) {
            clearstatcache(true, $this->get_scheme() . $path);
        }
        if ($result === false) {
            throw new File_System_Exception(new Phrase('The specified "%1" file couldn\'t be written. %2', [$path, $this->get_warning_message()]));
        }
        return $result;
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
        $result = @fopen($this->get_scheme() . $path, $mode);
        if ($this->stateful) {
            clearstatcache(true, $this->get_scheme() . $path);
        }
        if (!$result) {
            throw new File_System_Exception(new Phrase('File "%1" cannot be opened %2', [$path, $this->get_warning_message()]));
        }
        return $result;
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
        // phpcs:disable
        $result = @stream_get_line($resource, $length, $ending);
        // phpcs:enable
        if (false === $result) {
            throw new File_System_Exception(new Phrase('File cannot be read %1', [$this->get_warning_message()]));
        }
        return $result;
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
        $result = @fread($resource, $length);
        if ($result === false) {
            throw new File_System_Exception(new Phrase('File cannot be read %1', [$this->get_warning_message()]));
        }
        return $result;
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
        $result = @fgetcsv($resource, $length, $delimiter, $enclosure, $escape);
        if ($result === null) {
            throw new File_System_Exception(new Phrase('The "%1" CSV handle is incorrect. Verify the handle and try again.', [$this->get_warning_message()]));
        }
        return $result;
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
        $result = @ftell($resource);
        if ($result === null) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        $result = @fseek($resource, $offset, $whence);
        if ($result === -1) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" fileSeek execution.', [$this->get_warning_message()]));
        }
        return $result;
    }
    /**
     * Returns true if pointer at the end of file or in case of exception
     *
     * @param resource $resource
     * @return bool
     */
    public function end_of_file($resource)
    {
        return feof($resource);
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
        $result = @fclose($resource);
        if (!$result) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" fileClose execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        $data = $data !== null ? $data : '';
        $len_data = strlen($data);
        for ($result = 0; $result < $len_data; $result += $fwrite) {
            $fwrite = @fwrite($resource, substr($data, $result));
            if (0 === $fwrite) {
                $this->file_system_exception('Unable to write');
            }
            if (false === $fwrite) {
                $this->file_system_exception('An error occurred during "%1" fileWrite execution.', [$this->get_warning_message()]);
            }
        }
        return $result;
    }
    /**
     * Throw a FileSystemException with a Phrase of message and optional arguments
     *
     * @param string $message
     * @param array $arguments
     * @return void
     * @throws FileSystemException
     */
    private function file_system_exception($message, $arguments = [])
    {
        throw new File_System_Exception(new Phrase($message, $arguments));
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
        /**
         * Security enhancement for CSV data processing by Excel-like applications.
         * @see https://bugzilla.mozilla.org/show_bug.cgi?id=1054702
         *
         * @var $value string|Phrase
         */
        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                $value = (string) $value;
            }
            if (isset($value[0]) && in_array($value[0], ['=', '+', '-'])) {
                $data[$key] = ' ' . $value;
            }
        }
        // Escape symbol is needed to fix known issue in PHP broken fputcsv escaping functionality
        // where backslash followed by double quote breaks file consistency
        $escape = "\x00";
        $result = @fputcsv($resource, $data, $delimiter, $enclosure, $escape);
        if (!$result) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" filePutCsv execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        $result = @fflush($resource);
        if (!$result) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" fileFlush execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        $result = @flock($resource, $lock_mode);
        if (!$result) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" fileLock execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        $result = @flock($resource, LOCK_UN);
        if (!$result) {
            throw new File_System_Exception(new Phrase('An error occurred during "%1" fileUnlock execution.', [$this->get_warning_message()]));
        }
        return $result;
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
        // check if the path given is already an absolute path containing the
        // basepath. so if the basepath starts at position 0 in the path, we
        // must not concatinate them again because path is already absolute.
        $path = $path !== null ? $path : '';
        if ('' !== $base_path && strpos($path, (string) $base_path) === 0) {
            return $this->get_scheme($scheme) . $path;
        }
        return $this->get_scheme($scheme) . $base_path . ltrim($this->fix_separator($path), '/');
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
        $path = $path !== null ? $this->fix_separator($path) : '';
        if ($base_path === null || strpos($path, $base_path) === 0 || $base_path == $path . '/') {
            $result = substr($path, strlen($base_path));
        } else {
            $result = $path;
        }
        return $result;
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
        return str_replace('\\', '/', $path);
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
        $result = [];
        $flags = \Filesystem_Iterator::SKIP_DOTS | \Filesystem_Iterator::UNIX_PATHS | \Recursive_Directory_Iterator::FOLLOW_SYMLINKS;
        try {
            $iterator = new \Recursive_Iterator_Iterator(new \Recursive_Directory_Iterator($path, $flags), \Recursive_Iterator_Iterator::CHILD_FIRST);
            /** @var \FilesystemIterator $file */
            foreach ($iterator as $file) {
                $result[] = $file->get_pathname();
            }
        } catch (\Exception $e) {
            throw new File_System_Exception(new Phrase($e->get_message()), $e);
        }
        return $result;
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
        return realpath($path);
    }
    /**
     * Return correct path for link
     *
     * @param string $path
     * @return mixed
     */
    public function get_real_path_safety($path)
    {
        if ($path === null) {
            return '';
        }
        //Check backslashes
        $path = preg_replace('/\\\\+/', DIRECTORY_SEPARATOR, $path);
        //Removing redundant directory separators.
        $path = preg_replace('/\\' . DIRECTORY_SEPARATOR . '\\' . DIRECTORY_SEPARATOR . '+/', DIRECTORY_SEPARATOR, $path);
        if (strpos($path, DIRECTORY_SEPARATOR . '.') === false) {
            return rtrim($path, DIRECTORY_SEPARATOR);
        }
        $path_parts = explode(DIRECTORY_SEPARATOR, $path);
        if (end($path_parts) == '.') {
            $path_parts[count($path_parts) - 1] = '';
        }
        $real_path = [];
        foreach ($path_parts as $path_part) {
            if ($path_part == '.') {
                continue;
            }
            if ($path_part == '..') {
                array_pop($real_path);
                continue;
            }
            $real_path[] = $path_part;
        }
        return rtrim(implode(DIRECTORY_SEPARATOR, $real_path), DIRECTORY_SEPARATOR);
    }
}