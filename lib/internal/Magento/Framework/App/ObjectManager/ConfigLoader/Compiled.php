<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Object_Manager\Config_Loader;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Object_Manager\Config_Loader_Interface;
/**
 * Load configuration files
 */
class Compiled implements Config_Loader_Interface
{
    /**
     * Global config
     *
     * @var array
     */
    private $config_cache = [];
    /**
     * @inheritdoc
     */
    public function load($area)
    {
        if (isset($this->config_cache[$area])) {
            return $this->config_cache[$area];
        }
        $di_configuration = include self::get_file_path($area);
        $this->config_cache[$area] = $di_configuration;
        return $this->config_cache[$area];
    }
    /**
     * Returns path to compiled configuration
     *
     * @param string $area
     * @return string
     */
    public static function get_file_path($area)
    {
        $di_path = Directory_List::get_default_config()[Directory_List::GENERATED_METADATA][Directory_List::PATH];
        return BP . '/' . $di_path . '/' . $area . '.php';
    }
}