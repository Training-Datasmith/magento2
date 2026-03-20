<?php

declare (strict_types=1);
/**
 * Application config file resolver
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Filesystem\Directory_List;
class File_Resolver implements \Magento\Framework\Config\File_Resolver_Interface
{
    /**
     * Module configuration file reader
     *
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_module_reader;
    /**
     * File iterator factory
     *
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iterator_factory;
    /**
     * Filesystem
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;
    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $module_reader, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\Config\File_Iterator_Factory $iterator_factory)
    {
        $this->iterator_factory = $iterator_factory;
        $this->filesystem = $filesystem;
        $this->_module_reader = $module_reader;
    }
    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        switch ($scope) {
            case 'primary':
                $directory = $this->filesystem->get_directory_read(Directory_List::CONFIG);
                $absolute_paths = [];
                foreach ($directory->search('{' . $filename . ',*/' . $filename . '}') as $path) {
                    $absolute_paths[] = $directory->get_absolute_path($path);
                }
                $iterator = $this->iterator_factory->create($absolute_paths);
                break;
            case 'global':
                $iterator = $this->_module_reader->get_configuration_files($filename);
                break;
            default:
                $iterator = $this->_module_reader->get_configuration_files($scope . '/' . $filename);
                break;
        }
        return $iterator;
    }
}