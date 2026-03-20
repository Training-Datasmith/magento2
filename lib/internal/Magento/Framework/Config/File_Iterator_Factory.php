<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * @api
 * @since 100.0.2
 */
class File_Iterator_Factory
{
    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    private $file_read_factory;
    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory
     */
    public function __construct(\Magento\Framework\Filesystem\File\Read_Factory $file_read_factory)
    {
        $this->file_read_factory = $file_read_factory;
    }
    /**
     * Create file iterator
     *
     * @param array $paths List of absolute paths
     * @return FileIterator
     */
    public function create($paths)
    {
        return new File_Iterator($this->file_read_factory, $paths);
    }
}