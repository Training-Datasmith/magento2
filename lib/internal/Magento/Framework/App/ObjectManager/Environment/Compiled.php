<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Object_Manager\Environment;

use Magento\Framework\App\Area;
use Magento\Framework\App\Environment_Interface;
use Magento\Framework\App\Interception\Cache\Compiled_Config;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Interception\Object_Manager\Config_Interface;
use Magento\Framework\Object_Manager\Factory_Interface;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Compiled extends Abstract_Environment implements Environment_Interface
{
    /**#@+
     * Mode name
     */
    public const MODE = 'compiled';
    /**
     * @var string
     */
    protected $mode = self::MODE;
    /**
     * @var string
     */
    protected $config_preference = \Magento\Framework\Object_Manager\Factory\Compiled::class;
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled
     */
    private $config_loader;
    /**
     * Creates factory
     *
     * @param array $arguments
     * @param string $factoryClass
     *
     * @return FactoryInterface
     */
    protected function create_factory($arguments, $factory_class)
    {
        return new $factory_class($this->get_di_config(), $arguments['shared_instances'], $arguments);
    }
    /**
     * Returns initialized compiled config
     *
     * @return \Magento\Framework\Interception\ObjectManager\ConfigInterface
     */
    public function get_di_config()
    {
        if (!$this->config) {
            $this->config = new \Magento\Framework\Interception\Object_Manager\Config\Compiled($this->get_config_data());
        }
        return $this->config;
    }
    /**
     * Returns config data as array
     *
     * @return array
     */
    protected function get_config_data()
    {
        return $this->get_object_manager_config_loader()->load(Area::AREA_GLOBAL);
    }
    /**
     * Returns new instance of compiled config loader
     *
     * @return \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled
     */
    public function get_object_manager_config_loader()
    {
        if ($this->config_loader) {
            return $this->config_loader;
        }
        $this->config_loader = new \Magento\Framework\App\Object_Manager\Config_Loader\Compiled();
        return $this->config_loader;
    }
    /**
     * @inheritDoc
     */
    public function configure_object_manager(Config_Interface $di_config, &$shared_instances)
    {
        $object_manager = Object_Manager::get_instance();
        $object_manager->configure($object_manager->get(\Magento\Framework\Object_Manager\Config_Loader_Interface::class)->load(Area::AREA_GLOBAL));
        $object_manager->get(\Magento\Framework\Config\Scope_Interface::class)->set_current_scope('global');
        $di_config->set_interception_config($object_manager->get(\Magento\Framework\Interception\Config\Config::class));
        $shared_instances[\Magento\Framework\Interception\Plugin_List\Plugin_List::class] = $object_manager->create(\Magento\Framework\Interception\Plugin_List_Interface::class, ['cache' => $object_manager->get(\Magento\Framework\App\Interception\Cache\Compiled_Config::class)]);
        $object_manager->get(\Magento\Framework\App\Cache\Manager::class)->set_enabled([Compiled_Config::TYPE_IDENTIFIER], true);
    }
}