<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver_Pool;
/**
 * A target directory for remote filesystems.
 */
class Target_Directory
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $driver_code;
    /**
     * @param Filesystem $filesystem
     * @param string $driverCode
     */
    public function __construct(Filesystem $filesystem, $driver_code = Filesystem\Driver_Pool::FILE)
    {
        $this->filesystem = $filesystem;
        $this->driver_code = $driver_code;
    }
    /**
     * Create an instance of directory with write permissions.
     *
     * @param string $directoryCode
     * @return WriteInterface
     * @throws FileSystemException
     */
    public function get_directory_write(string $directory_code): Write_Interface
    {
        return $this->filesystem->get_directory_write($directory_code, $this->driver_code);
    }
    /**
     * Create an instance of directory with read permissions.
     *
     * @param string $directoryCode
     * @return ReadInterface
     */
    public function get_directory_read(string $directory_code): Read_Interface
    {
        return $this->filesystem->get_directory_read($directory_code, $this->driver_code);
    }
    /**
     * Create an instance of directory with read permissions with file path.
     *
     * @param String $path
     * @param string $driverCode
     * @return ReadInterface
     */
    public function get_directory_read_by_path(string $path, $driver_code = Driver_Pool::FILE): Read_Interface
    {
        return $this->filesystem->get_directory_read_by_path($path, $driver_code);
    }
}