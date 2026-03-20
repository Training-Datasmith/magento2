<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\Interception\Object_Manager\Config_Interface;
use Magento\Framework\Object_Manager\Config_Loader_Interface;
use Magento\Framework\Object_Manager\Factory_Interface;
/**
 * Interface for ObjectManager Environment
 *
 * @api
 */
interface Environment_Interface
{
    /**
     * Return name of running mode
     *
     * @return string
     */
    public function get_mode();
    /**
     * Return config object
     *
     * @return ConfigInterface
     */
    public function get_di_config();
    /**
     * Return factory object
     *
     * @param array $arguments
     * @return FactoryInterface
     */
    public function get_object_manager_factory($arguments);
    /**
     * Return ConfigLoader object
     *
     * @return ConfigLoaderInterface
     */
    public function get_object_manager_config_loader();
    /**
     * @param ConfigInterface $diConfig
     * @param array &$sharedInstances
     * @return void
     */
    public function configure_object_manager(Config_Interface $di_config, &$shared_instances);
}