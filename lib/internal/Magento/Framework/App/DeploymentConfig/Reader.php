<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Deployment_Config;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem\Driver_Pool;
use Magento\Framework\Phrase;
/**
 * Deployment configuration reader.
 * Loads the merged configuration from config files.
 * @see FileReader The reader for specific configuration file
 */
class Reader
{
    /**
     * @var DirectoryList
     */
    private $dir_list;
    /**
     * @var ConfigFilePool
     */
    private $config_file_pool;
    /**
     * @var DriverPool
     */
    private $driver_pool;
    /**
     * Configuration file names
     *
     * @var array
     */
    private $files;
    /**
     * Constructor
     *
     * @param DirectoryList $dirList
     * @param DriverPool $driverPool
     * @param ConfigFilePool $configFilePool
     * @param null|string $file
     * @throws \InvalidArgumentException
     */
    public function __construct(Directory_List $dir_list, Driver_Pool $driver_pool, Config_File_Pool $config_file_pool, $file = null)
    {
        $this->dir_list = $dir_list;
        $this->config_file_pool = $config_file_pool;
        $this->driver_pool = $driver_pool;
        if (null !== $file) {
            if (!preg_match('/^[a-z\d\.\-]+\.php$/i', $file)) {
                throw new \InvalidArgumentException("Invalid file name: {$file}");
            }
            $this->files = [$file];
        } else {
            $this->files = $this->config_file_pool->get_paths();
        }
    }
    /**
     * Gets the file name
     *
     * @return array
     */
    public function get_files()
    {
        return $this->files;
    }
    /**
     * Method loads merged configuration within all configuration files.
     * To retrieve specific file configuration, use FileReader.
     * $fileKey option is deprecated since version 2.2.0.
     *
     * @param string $fileKey The file key (deprecated)
     * @return array
     * @throws FileSystemException If file can not be read
     * @throws RuntimeException If file is invalid
     * @throws \Exception If file key is not correct
     * @see FileReader
     */
    public function load($file_key = null)
    {
        $path = $this->dir_list->get_path(Directory_List::CONFIG);
        $file_driver = $this->driver_pool->get_driver(Driver_Pool::FILE);
        $result = [];
        if ($file_key) {
            $file_path = $path . '/' . $this->config_file_pool->get_path($file_key);
            if ($file_driver->is_exists($file_path)) {
                $result = include $file_path;
                if (!is_array($result)) {
                    throw new RuntimeException(new Phrase("Invalid configuration file: '%1'", [$file_path]));
                }
            }
        } else {
            $config_files = $this->get_files();
            foreach ($config_files as $file) {
                $config_file = $path . '/' . $file;
                if ($file_driver->is_exists($config_file)) {
                    $file_data = include $config_file;
                    if (!is_array($file_data)) {
                        throw new RuntimeException(new Phrase("Invalid configuration file: '%1'", [$config_file]));
                    }
                } else {
                    continue;
                }
                if ($file_data) {
                    $result = array_replace_recursive($result, $file_data);
                }
            }
        }
        return $result ?: [];
    }
}