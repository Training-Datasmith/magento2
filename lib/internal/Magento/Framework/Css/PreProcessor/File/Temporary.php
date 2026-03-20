<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor\File;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Css\Pre_Processor\Config;
use Magento\Framework\Filesystem;
class Temporary
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $tmp_directory;
    /**
     * @param Filesystem $filesystem
     * @param Config $config
     */
    public function __construct(Filesystem $filesystem, Config $config)
    {
        $this->tmp_directory = $filesystem->get_directory_write(Directory_List::VAR_DIR);
        $this->config = $config;
    }
    /**
     * Write down contents to a temporary file and return its absolute path
     *
     * @param string $relativePath
     * @param string $contents
     * @return string
     */
    public function create_file($relative_path, $contents)
    {
        $file_path = $this->config->get_materialization_relative_path() . '/' . $relative_path;
        if (!$this->tmp_directory->is_exist($file_path)) {
            $this->tmp_directory->write_file($file_path, $contents);
        }
        return $this->tmp_directory->get_absolute_path($file_path);
    }
}