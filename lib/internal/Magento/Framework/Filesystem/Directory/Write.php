<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\Validator_Exception;
use Magento\Framework\Filesystem\Driver_Interface;
use Magento\Framework\Phrase;
/**
 * Write Interface implementation
 */
class Write extends Read implements Write_Interface
{
    /**
     * Permissions for new sub-directories
     *
     * @var int
     */
    protected $permissions = 0777;
    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\File\WriteFactory $fileFactory
     * @param DriverInterface $driver
     * @param string $path
     * @param int $createPermissions
     * @param PathValidatorInterface|null $pathValidator
     */
    public function __construct(\Magento\Framework\Filesystem\File\Write_Factory $file_factory, Driver_Interface $driver, $path, ?int $create_permissions = null, ?Path_Validator_Interface $path_validator = null)
    {
        parent::__construct($file_factory, $driver, $path, $path_validator);
        if (null !== $create_permissions) {
            $this->permissions = $create_permissions;
        }
    }
    /**
     * Check if directory or file is writable
     *
     * @param string $path
     * @return void
     * @throws FileSystemException|ValidatorException
     */
    protected function assert_writable($path)
    {
        $this->validate_path($path);
        if ($this->is_writable($path) === false) {
            $path = $this->get_absolute_path($path);
            throw new File_System_Exception(new Phrase('The path "%1" is not writable.', [$path]));
        }
    }
    /**
     * Check if given path is exists and is file
     *
     * @param string $path
     * @return void
     * @throws FileSystemException
     */
    protected function assert_is_file($path)
    {
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        clearstatcache(true, $absolute_path);
        if (!$this->driver->is_file($absolute_path)) {
            throw new File_System_Exception(new Phrase('The "%1" file doesn\'t exist.', [$absolute_path]));
        }
    }
    /**
     * Create directory if it does not exist
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function create($path = null)
    {
        $this->validate_path($path);
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        if ($this->driver->is_directory($absolute_path)) {
            return true;
        }
        return $this->driver->create_directory($absolute_path, $this->permissions);
    }
    /**
     * Rename a file
     *
     * @param string $path
     * @param string $newPath
     * @param WriteInterface $targetDirectory
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function rename_file($path, $new_path, Write_Interface|null $target_directory = null)
    {
        $this->validate_path($path);
        $this->validate_path($new_path);
        $this->assert_is_file($path);
        $target_directory = $target_directory ?: $this;
        if (!$target_directory->is_exist($this->driver->get_parent_directory($new_path))) {
            $target_directory->create($this->driver->get_parent_directory($new_path));
        }
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        $absolute_new_path = $target_directory->get_absolute_path($new_path);
        return $this->driver->rename($absolute_path, $absolute_new_path, $target_directory->get_driver());
    }
    /**
     * Copy a file
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface|null $targetDirectory
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function copy_file($path, $destination, Write_Interface|null $target_directory = null)
    {
        $this->validate_path($path);
        $this->validate_path($destination);
        $this->assert_is_file($path);
        $target_directory = $target_directory ?: $this;
        if (!$target_directory->is_exist($this->driver->get_parent_directory($destination))) {
            $target_directory->create($this->driver->get_parent_directory($destination));
        }
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        $absolute_destination = $target_directory->get_absolute_path($destination);
        return $this->driver->copy($absolute_path, $absolute_destination, $target_directory->driver);
    }
    /**
     * Creates symlink on a file and places it to destination
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface|null $targetDirectory [optional]
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function create_symlink($path, $destination, Write_Interface|null $target_directory = null)
    {
        $this->validate_path($path);
        $this->validate_path($destination);
        $target_directory = $target_directory ?: $this;
        $parent_directory = $this->driver->get_parent_directory($destination);
        if (!$target_directory->is_exist($parent_directory)) {
            $target_directory->create($parent_directory);
        }
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        $absolute_destination = $target_directory->get_absolute_path($destination);
        return $this->driver->symlink($absolute_path, $absolute_destination, $this->driver);
    }
    /**
     * Delete given path
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function delete($path = null)
    {
        $exception_messages = [];
        $this->validate_path($path);
        if (!$this->is_exist($path)) {
            return true;
        }
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        $base_path = $this->driver->get_real_path_safety($this->driver->get_absolute_path($this->path, ''));
        if ($path !== null && $path !== '' && $this->driver->get_real_path_safety($absolute_path) === $base_path) {
            throw new File_System_Exception(new Phrase('The path "%1" is not writable.', [$path]));
        }
        if ($this->driver->is_file($absolute_path)) {
            $this->driver->delete_file($absolute_path);
        } else {
            try {
                $this->delete_files_recursively($absolute_path);
            } catch (File_System_Exception $e) {
                $exception_messages[] = $e->get_message();
            }
            try {
                $this->driver->delete_directory($absolute_path);
            } catch (File_System_Exception $e) {
                $exception_messages[] = $e->get_message();
            }
            if (!empty($exception_messages)) {
                throw new File_System_Exception(new Phrase(\implode(' ', $exception_messages)));
            }
        }
        return true;
    }
    /**
     * Delete files recursively
     *
     * Implemented in order to delete as much files as possible and collect all exceptions
     *
     * @param string $path
     * @return void
     * @throws FileSystemException
     */
    private function delete_files_recursively(string $path)
    {
        $exception_messages = [];
        $entities_list = $this->driver->read_directory_recursively($path);
        foreach ($entities_list as $entity_path) {
            if ($this->driver->is_file($entity_path)) {
                try {
                    $this->validate_path($entity_path);
                    $this->driver->delete_file($entity_path);
                } catch (File_System_Exception|Validator_Exception $e) {
                    $exception_messages[] = $e->get_message();
                }
            }
        }
        if (!empty($exception_messages)) {
            throw new File_System_Exception(new Phrase(\implode(' ', $exception_messages)));
        }
    }
    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function change_permissions($path, $permissions)
    {
        $this->validate_path($path);
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        return $this->driver->change_permissions($absolute_path, $permissions);
    }
    /**
     * Recursively change permissions of given path
     *
     * @param string $path
     * @param int $dirPermissions
     * @param int $filePermissions
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function change_permissions_recursively($path, $dir_permissions, $file_permissions)
    {
        $this->validate_path($path);
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        return $this->driver->change_permissions_recursively($absolute_path, $dir_permissions, $file_permissions);
    }
    /**
     * Sets modification time of file, if file does not exist - creates file
     *
     * @param string $path
     * @param int|null $modificationTime
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function touch($path, $modification_time = null)
    {
        $this->validate_path($path);
        $folder = $this->driver->get_parent_directory($path);
        $this->create($folder);
        $this->assert_writable($folder);
        return $this->driver->touch($this->driver->get_absolute_path($this->path, $path), $modification_time);
    }
    /**
     * Check if given path is writable
     *
     * @param string|null $path
     * @return bool
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function is_writable($path = null)
    {
        $this->validate_path($path);
        return $this->driver->is_writable($this->driver->get_absolute_path($this->path, $path));
    }
    /**
     * Open file in given mode
     *
     * @param string $path
     * @param string $mode
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function open_file($path, $mode = 'w')
    {
        $this->validate_path($path);
        if ($path === null || $path === '') {
            throw new File_System_Exception(new Phrase('Invalid file path: path cannot be null or empty'));
        }
        $folder = dirname($path);
        $this->create($folder);
        $this->assert_writable($this->is_exist($path) ? $path : $folder);
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        return $this->file_factory->create($absolute_path, $this->driver, $mode);
    }
    /**
     * Write contents to file in given mode
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @param bool $lock
     * @return int The number of bytes that were written.
     * @throws FileSystemException|ValidatorException
     */
    public function write_file($path, $content, $mode = 'w+', bool $lock = false)
    {
        $this->validate_path($path);
        $file = $this->open_file($path, $mode);
        try {
            if ($lock) {
                $file->lock();
            }
            $result = $file->write($content);
        } finally {
            if ($lock) {
                $file->unlock();
            }
        }
        $file->close();
        return $result;
    }
    /**
     * Get driver
     *
     * @return DriverInterface
     */
    public function get_driver()
    {
        return $this->driver;
    }
}