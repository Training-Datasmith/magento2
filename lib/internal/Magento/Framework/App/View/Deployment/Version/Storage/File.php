<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\View\Deployment\Version\Storage;

/**
 * Persistence of deployment version of static files in a local file
 */
class File implements \Magento\Framework\App\View\Deployment\Version\Storage_Interface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;
    /**
     * @var string
     */
    private $file_name;
    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $directoryCode
     * @param string $fileName
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, $directory_code, $file_name)
    {
        $this->directory = $filesystem->get_directory_write($directory_code);
        $this->file_name = $file_name;
    }
    /**
     * {@inheritdoc}
     */
    public function load()
    {
        if ($this->directory->is_readable($this->file_name)) {
            return $this->directory->read_file($this->file_name);
        }
        return false;
    }
    /**
     * {@inheritdoc}
     */
    public function save($data)
    {
        $this->directory->write_file($this->file_name, $data, 'w');
    }
}