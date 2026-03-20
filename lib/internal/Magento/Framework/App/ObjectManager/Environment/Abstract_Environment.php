<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Object_Manager\Environment;

use Magento\Framework\App\Environment_Factory;
use Magento\Framework\App\Environment_Interface;
use Magento\Framework\Interception\Object_Manager\Config_Interface;
use Magento\Framework\Object_Manager\Factory_Interface;
use Magento\Framework\Object_Manager\Profiler\Factory_Decorator;
use Magento\Framework\Object_Manager\Profiler\Log;
abstract class Abstract_Environment implements Environment_Interface
{
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * Mode name
     */
    protected $mode = 'developer';
    /**
     * @var string
     */
    protected $config_preference = \Magento\Framework\Object_Manager\Factory\Dynamic\Developer::class;
    /**
     * @var FactoryInterface
     */
    protected $factory;
    /**
     * @var EnvironmentFactory
     */
    protected $env_factory;
    /**
     * @param EnvironmentFactory $envFactory
     */
    public function __construct(Environment_Factory $env_factory)
    {
        $this->env_factory = $env_factory;
    }
    /**
     * Returns object manager factory
     *
     * @param array $arguments
     * @return FactoryInterface
     */
    public function get_object_manager_factory($arguments)
    {
        $factory_class = $this->get_di_config()->get_preference($this->config_preference);
        $this->factory = $this->create_factory($arguments, $factory_class);
        $this->decorate($arguments);
        return $this->factory;
    }
    /**
     * Return name of running mode
     *
     * @return string
     */
    public function get_mode()
    {
        return $this->mode;
    }
    /**
     * Decorate factory
     *
     * @param array $arguments
     * @return void
     */
    protected function decorate($arguments)
    {
        if (isset($arguments['MAGE_PROFILER']) && $arguments['MAGE_PROFILER'] == 2) {
            $this->factory = new Factory_Decorator($this->factory, Log::get_instance());
        }
    }
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
        return new $factory_class($this->get_di_config(), null, $this->env_factory->get_definitions(), $arguments);
    }
}