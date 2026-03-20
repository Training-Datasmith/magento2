<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Filesystem;

use Magento\Framework\Filesystem;
/**
 * Magento directories resolver.
 */
class Directory_Resolver
{
    /**
     * @var DirectoryList
     * @deprecated $this->filesystem->getDirectoryWrite() can be used for getting directory
     */
    private $directory_list;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     */
    public function __construct(Directory_List $directory_list, Filesystem $filesystem)
    {
        $this->directory_list = $directory_list;
        $this->filesystem = $filesystem;
    }
    /**
     * Validate path.
     *
     * Gets real path for directory provided in parameters and compares it with specified root directory.
     * Will return TRUE if real path of provided value contains root directory path and FALSE if not.
     * Throws the \Magento\Framework\Exception\FileSystemException in case when directory path is absent
     * in Directories configuration.
     *
     * @param string $path
     * @param string $directoryConfig
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function validate_path($path, $directory_config = Directory_List::MEDIA)
    {
        $directory = $this->filesystem->get_directory_write($directory_config);
        $real_path = $directory->get_driver()->get_real_path_safety($path);
        $root = rtrim($directory->get_absolute_path(), DIRECTORY_SEPARATOR);
        return strpos($real_path, $root) === 0;
    }
}