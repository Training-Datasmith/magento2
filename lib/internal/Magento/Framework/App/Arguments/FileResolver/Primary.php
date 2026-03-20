<?php

declare (strict_types=1);
/**
 * Application primary config file resolver
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Arguments\File_Resolver;

use Magento\Framework\App\Filesystem\Directory_List;
class Primary implements \Magento\Framework\Config\File_Resolver_Interface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $config_directory;
    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iterator_factory;
    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, \Magento\Framework\Config\File_Iterator_Factory $iterator_factory)
    {
        $this->config_directory = $filesystem->get_directory_read(Directory_List::CONFIG);
        $this->iterator_factory = $iterator_factory;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($filename, $scope)
    {
        $config_paths = $this->config_directory->search('{*' . $filename . ',*/*' . $filename . '}');
        $config_absolute_paths = [];
        foreach ($config_paths as $config_path) {
            $config_absolute_paths[] = $this->config_directory->get_absolute_path($config_path);
        }
        return $this->iterator_factory->create($config_absolute_paths);
    }
}