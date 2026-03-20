<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver_Interface;
/**
 * Utility for generating a unique file name
 */
class Name
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @param Filesystem|null $filesystem
     */
    public function __construct(?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: Object_Manager::get_instance()->get(Filesystem::class);
    }
    /**
     * Gets new file name if the given name is in use
     *
     * @param string $destinationFile
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function get_new_file_name(string $destination_file)
    {
        $file_info = $this->get_path_info($destination_file);
        $driver = $this->filesystem->get_directory_write(Directory_List::ROOT, Filesystem\Driver_Pool::FILE)->get_driver();
        if ($driver->is_exists($destination_file)) {
            return $this->generate_file_name($driver, $file_info);
        }
        /**
         * Try with non-local driver.
         */
        $driver = $this->filesystem->get_directory_write(Directory_List::ROOT)->get_driver();
        return $driver->is_exists($destination_file) ? $this->generate_file_name($driver, $file_info) : $file_info['basename'];
    }
    /**
     * Generates new file name until file with provided name doesn't exist
     *
     * @param DriverInterface $driver
     * @param string $fileInfo
     * @param int $index
     * @return string
     * @throws FileSystemException
     */
    private function generate_file_name($driver, $file_info, $index = 1)
    {
        $base_name = $file_info['filename'] . '_' . $index . '.' . $file_info['extension'];
        if ($driver->is_exists($file_info['dirname'] . '/' . $base_name)) {
            return $this->generate_file_name($driver, $file_info, ++$index);
        }
        return $base_name;
    }
    /**
     * Gets the path information from a given file
     *
     * @param string $destinationFile
     * @return string|string[]
     */
    private function get_path_info(string $destination_file)
    {
        return pathinfo($destination_file);
    }
}