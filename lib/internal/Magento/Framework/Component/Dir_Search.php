<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Component;

use Magento\Framework\Filesystem;
/**
 * Class for searching files across all locations of certain component type
 */
class Dir_Search
{
    /**
     * Component registrar
     *
     * @var ComponentRegistrarInterface
     */
    private $registrar;
    /**
     * Read dir factory
     *
     * @var Filesystem\Directory\ReadFactory
     */
    private $read_factory;
    /**
     * Constructor
     *
     * @param ComponentRegistrarInterface $registrar
     * @param Filesystem\Directory\ReadFactory $readFactory
     */
    public function __construct(Component_Registrar_Interface $registrar, Filesystem\Directory\Read_Factory $read_factory)
    {
        $this->registrar = $registrar;
        $this->read_factory = $read_factory;
    }
    /**
     * Search for files in each component by pattern, returns absolute paths
     *
     * @param string $componentType
     * @param string $pattern
     * @return array
     */
    public function collect_files($component_type, $pattern)
    {
        return $this->collect($component_type, $pattern, false);
    }
    /**
     * Search for files in each component by pattern, returns file objects with absolute file paths
     *
     * @param string $componentType
     * @param string $pattern
     * @return ComponentFile[]
     */
    public function collect_files_with_context($component_type, $pattern)
    {
        return $this->collect($component_type, $pattern, true);
    }
    /**
     * Collect files in components
     * If $withContext is true, returns array of file objects with component context
     *
     * @param string $componentType
     * @param string $pattern
     * @param bool|false $withContext
     * @return array
     */
    private function collect($component_type, $pattern, $with_context)
    {
        $files = [];
        foreach ($this->registrar->get_paths($component_type) as $component_name => $path) {
            $directory_read = $this->read_factory->create($path);
            $found_files = $directory_read->search($pattern);
            foreach ($found_files as $found_file) {
                $found_file = $directory_read->get_absolute_path($found_file);
                if ($with_context) {
                    $files[] = new Component_File($component_type, $component_name, $found_file);
                } else {
                    $files[] = $found_file;
                }
            }
        }
        return $files;
    }
}