<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
/**
 * Class GenericSchemaLocator
 */
class Generic_Schema_Locator implements Schema_Locator_Interface
{
    /**
     * @var ModuleDirReader
     */
    private $module_dir_reader;
    /**
     * @var string
     */
    private $module_name;
    /**
     * @var string
     */
    private $per_file_schema;
    /**
     * @var string|null
     */
    private $schema;
    /**
     * @param ModuleDirReader $reader
     * @param string $moduleName
     * @param string $schema
     * @param string|null $perFileSchema
     */
    public function __construct(Module_Dir_Reader $reader, $module_name, $schema, $per_file_schema = null)
    {
        $this->module_dir_reader = $reader;
        $this->module_name = $module_name;
        $this->schema = $schema;
        $this->per_file_schema = $per_file_schema;
    }
    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function get_schema()
    {
        return $this->module_dir_reader->get_module_dir(Dir::MODULE_ETC_DIR, $this->module_name) . '/' . $this->schema;
    }
    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function get_per_file_schema()
    {
        if ($this->per_file_schema !== null) {
            return $this->module_dir_reader->get_module_dir(Dir::MODULE_ETC_DIR, $this->module_name) . '/' . $this->per_file_schema;
        }
    }
}