<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Deployment_Config;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem\Driver_Pool;
/**
 * Allows to read configurations from different config files.
 *
 * @see Reader The reader for merged configurations
 */
class File_Reader
{
    /**
     * The list of directories.
     *
     * @var DirectoryList
     */
    private $dir_list;
    /**
     * The pool of config files.
     *
     * @var ConfigFilePool
     */
    private $config_file_pool;
    /**
     * The pool of stream drivers.
     *
     * @var DriverPool
     */
    private $driver_pool;
    /**
     * @param DirectoryList $dirList The list of directories
     * @param DriverPool $driverPool The pool of config files
     * @param ConfigFilePool $configFilePool The pool of stream drivers
     */
    public function __construct(Directory_List $dir_list, Driver_Pool $driver_pool, Config_File_Pool $config_file_pool)
    {
        $this->dir_list = $dir_list;
        $this->config_file_pool = $config_file_pool;
        $this->driver_pool = $driver_pool;
    }
    /**
     * Loads the configuration file.
     *
     * @param string $fileKey The file key
     * @return array The configurations array
     * @throws FileSystemException If file can not be read
     * @throws \Exception If file key is not correct
     */
    public function load($file_key)
    {
        $path = $this->dir_list->get_path(Directory_List::CONFIG);
        $file_driver = $this->driver_pool->get_driver(Driver_Pool::FILE);
        $file_path = $path . '/' . $this->config_file_pool->get_path($file_key);
        if ($file_driver->is_exists($file_path)) {
            return include $file_path;
        }
        return [];
    }
}