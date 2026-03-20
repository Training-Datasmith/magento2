<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Console;

use Laminas\Service_Manager\Service_Manager;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Filesystem\Directory\Write_Factory;
use Magento\Framework\Filesystem\Driver_Pool;
use Magento\Setup\Mvc\Bootstrap\Init_Param_Listener;
/**
 * Check generated/code read and write access
 */
class Generation_Directory_Access
{
    /**
     * @var ServiceManager
     */
    private $service_manager;
    /**
     * @param ServiceManager $serviceManager
     */
    public function __construct(Service_Manager $service_manager)
    {
        $this->service_manager = $service_manager;
    }
    /**
     * Check write permissions to generation folders
     *
     * @return bool
     */
    public function check()
    {
        $init_params = $this->service_manager->get(Init_Param_Listener::BOOTSTRAP_PARAM);
        $filesystem_dir_paths = isset($init_params[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]) ? $init_params[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] : [];
        $directory_list = new Directory_List(BP, $filesystem_dir_paths);
        $driver_pool = new Driver_Pool();
        $file_write_factory = new Write_Factory($driver_pool);
        $generation_dirs = [Directory_List::GENERATED, Directory_List::GENERATED_CODE, Directory_List::GENERATED_METADATA];
        foreach ($generation_dirs as $generation_directory) {
            $directory_path = $directory_list->get_path($generation_directory);
            $directory_write = $file_write_factory->create($directory_path);
            if (!$directory_write->is_exist()) {
                try {
                    $directory_write->create();
                } catch (\Exception $e) {
                    return false;
                }
            }
            if (!$directory_write->is_writable()) {
                return false;
            }
        }
        return true;
    }
}