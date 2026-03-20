<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Object_Manager\Environment;

use Magento\Framework\App\Area;
use Magento\Framework\App\Environment_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Interception\Object_Manager\Config_Interface;
class Developer extends Abstract_Environment implements Environment_Interface
{
    /**#@+
     * Mode name
     */
    public const MODE = 'developer';
    /**
     * @var string
     */
    protected $mode = self::MODE;
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var string
     */
    protected $config_preference = \Magento\Framework\Object_Manager\Factory\Dynamic\Developer::class;
    /**
     * Returns initialized di config entity
     *
     * @return ConfigInterface
     */
    public function get_di_config()
    {
        if (!$this->config) {
            $this->config = new \Magento\Framework\Interception\Object_Manager\Config\Developer($this->env_factory->get_relations(), $this->env_factory->get_definitions());
        }
        return $this->config;
    }
    /**
     * As developer environment does not have config loader, we return null
     *
     * @return null
     */
    public function get_object_manager_config_loader()
    {
        return null;
    }
    /**
     * @inheritDoc
     */
    public function configure_object_manager(Config_Interface $di_config, &$shared_instances)
    {
        $original_shared_instances = $shared_instances;
        $object_manager = Object_Manager::get_instance();
        $shared_instances[\Magento\Framework\Object_Manager\Config_Loader_Interface::class] = $object_manager->get(\Magento\Framework\App\Object_Manager\Config_Loader::class);
        $di_config->set_cache($object_manager->get(\Magento\Framework\App\Object_Manager\Config_Cache::class));
        $object_manager->configure($object_manager->get(\Magento\Framework\App\Object_Manager\Config_Loader::class)->load(Area::AREA_GLOBAL));
        $object_manager->get(\Magento\Framework\Config\Scope_Interface::class)->set_current_scope('global');
        $di_config->set_interception_config($object_manager->get(\Magento\Framework\Interception\Config\Config::class));
        /** Reset the shared instances once interception config is set so classes can be intercepted if necessary */
        $shared_instances = $original_shared_instances;
        $shared_instances[\Magento\Framework\Object_Manager\Config_Loader_Interface::class] = $object_manager->get(\Magento\Framework\App\Object_Manager\Config_Loader::class);
    }
}