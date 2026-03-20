<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Component\Component_Registrar;
use Magento\Framework\Filesystem\Driver_Interface;
use Magento\Framework\Module\Dir;
/**
 * Application config file resolver.
 */
class File_Resolver_By_Module extends \Magento\Framework\App\Config\File_Resolver
{
    /**
     * This flag says, that we need to read from all modules.
     */
    public const ALL_MODULES = 'all';
    /**
     * @var ComponentRegistrar
     */
    private $component_registrar;
    /**
     * @var DriverInterface
     */
    private $driver;
    /**
     * Constructor.
     *
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param FileIteratorFactory $iteratorFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $module_reader, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\Config\File_Iterator_Factory $iterator_factory, Component_Registrar $component_registrar, \Magento\Framework\Filesystem\Driver\File $driver)
    {
        parent::__construct($module_reader, $filesystem, $iterator_factory);
        $this->component_registrar = $component_registrar;
        $this->driver = $driver;
    }
    /**
     * If scope is module.
     *
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        $iterator = $this->_module_reader->get_configuration_files($filename)->to_array();
        if ($scope !== self::ALL_MODULES) {
            $path = $this->component_registrar->get_path('module', $scope);
            $path .= '/' . Dir::MODULE_ETC_DIR . '/' . $filename;
            $iterator = isset($iterator[$path]) ? [$path => $iterator[$path]] : [];
        }
        $primary_file = parent::get($filename, 'primary')->to_array();
        if (!$this->driver->is_file(key($primary_file))) {
            throw new \Exception('Primary db_schema file doesn`t exists');
        }
        /** Load primary configurations */
        $iterator += $primary_file;
        return $iterator;
    }
}