<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\Validator_Exception;
/**
 * Filesystem directory instance for read operations
 * @api
 * @since 100.0.2
 */
class Read implements Read_Interface
{
    /**
     * Directory path
     *
     * @var string
     */
    protected $path;
    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected $file_factory;
    /**
     * Filesystem driver
     *
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected $driver;
    /**
     * @var PathValidatorInterface|null
     */
    private $path_validator;
    /**
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileFactory
     * @param \Magento\Framework\Filesystem\DriverInterface $driver
     * @param string $path
     * @param PathValidatorInterface|null $pathValidator
     */
    public function __construct(\Magento\Framework\Filesystem\File\Read_Factory $file_factory, \Magento\Framework\Filesystem\Driver_Interface $driver, $path, ?Path_Validator_Interface $path_validator = null)
    {
        $this->file_factory = $file_factory;
        $this->driver = $driver;
        $this->set_path($path);
        $this->path_validator = $path_validator;
    }
    /**
     * Validate the path is correct and within the directory
     *
     * @param null|string $path
     * @param null|string $scheme
     * @param bool $absolutePath
     * @throws ValidatorException
     *
     * @return void
     * @since 101.0.7
     */
    protected function validate_path(?string $path, ?string $scheme = null, bool $absolute_path = false): void
    {
        if ($path && $this->path_validator) {
            $this->path_validator->validate($this->path, $path, $scheme, $absolute_path);
        }
    }
    /**
     * Sets base path
     *
     * @param string $path
     * @return void
     */
    protected function set_path($path)
    {
        if (!empty($path)) {
            $this->path = rtrim(str_replace('\\', '/', $path), '/') . '/';
        }
    }
    /**
     * Retrieves absolute path i.e. /var/www/application/file.txt
     *
     * @param string $path
     * @param string $scheme
     * @throws ValidatorException
     * @return string
     */
    public function get_absolute_path($path = null, $scheme = null)
    {
        $this->validate_path($path, $scheme);
        return $this->driver->get_absolute_path($this->path, $path, $scheme);
    }
    /**
     * Retrieves relative path
     *
     * @param string $path
     * @throws ValidatorException
     * @return string
     */
    public function get_relative_path($path = null)
    {
        $this->validate_path($path, null, $path && $path[0] === DIRECTORY_SEPARATOR);
        return $this->driver->get_relative_path($this->path, $path);
    }
    /**
     * Retrieve list of all entities in given path
     *
     * @param string|null $path
     * @throws ValidatorException
     * @return string[]
     */
    public function read($path = null)
    {
        $this->validate_path($path);
        $files = $this->driver->read_directory($this->driver->get_absolute_path($this->path, $path));
        $result = [];
        foreach ($files as $file) {
            $result[] = $this->get_relative_path($file);
        }
        return $result;
    }
    /**
     * Read recursively
     *
     * @param string|null $path
     * @throws ValidatorException
     * @return string[]
     */
    public function read_recursively($path = null)
    {
        $this->validate_path($path);
        $result = [];
        $paths = $this->driver->read_directory_recursively($this->driver->get_absolute_path($this->path, $path));
        /** @var \FilesystemIterator $file */
        foreach ($paths as $file) {
            $result[] = $this->get_relative_path($file);
        }
        sort($result);
        return $result;
    }
    /**
     * Search all entries for given regex pattern
     *
     * @param string $pattern
     * @param string $path [optional]
     * @throws ValidatorException
     * @return string[]
     */
    public function search($pattern, $path = null)
    {
        $this->validate_path($path);
        if ($path) {
            $absolute_path = $this->driver->get_absolute_path($this->path, $this->get_relative_path($path));
        } else {
            $absolute_path = $this->path;
        }
        $files = $this->driver->search($pattern, $absolute_path);
        $result = [];
        foreach ($files as $file) {
            $result[] = $this->get_relative_path($file);
        }
        return $result;
    }
    /**
     * Check a file or directory exists
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws ValidatorException
     */
    public function is_exist($path = null)
    {
        $this->validate_path($path);
        return $this->driver->is_exists($this->driver->get_real_path_safety($this->driver->get_absolute_path($this->path, $path)));
    }
    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws ValidatorException
     */
    public function stat($path)
    {
        $this->validate_path($path);
        return $this->driver->stat($this->driver->get_absolute_path($this->path, $path));
    }
    /**
     * Check permissions for reading file or directory
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws ValidatorException
     */
    public function is_readable($path = null)
    {
        $this->validate_path($path);
        return $this->driver->is_readable($this->driver->get_absolute_path($this->path, $path));
    }
    /**
     * Open file in read mode
     *
     * @param string $path
     * @throws ValidatorException
     *
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     */
    public function open_file($path)
    {
        $this->validate_path($path);
        return $this->file_factory->create($this->driver->get_absolute_path($this->path, $path), $this->driver);
    }
    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function read_file($path, $flag = null, $context = null)
    {
        $this->validate_path($path);
        $absolute_path = $this->driver->get_absolute_path($this->path, $path);
        return $this->driver->file_get_contents($absolute_path, $flag, $context);
    }
    /**
     * Check whether given path is file
     *
     * @param string $path
     * @throws ValidatorException
     * @return bool
     */
    public function is_file($path)
    {
        $this->validate_path($path);
        return $this->driver->is_file($this->driver->get_absolute_path($this->path, $path));
    }
    /**
     * Check whether given path is directory
     *
     * @param string $path [optional]
     * @throws ValidatorException
     * @return bool
     */
    public function is_directory($path = null)
    {
        $this->validate_path($path);
        return $this->driver->is_directory($this->driver->get_absolute_path($this->path, $path));
    }
    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return ['path' => $this->path];
    }
}