<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\App\Arguments\Argument_Interpreter;
use Magento\Framework\App\Arguments\File_Resolver\Primary;
use Magento\Framework\App\Arguments\Validation_State;
use Magento\Framework\App\Cache\Frontend\Factory as CacheFrontendFactory;
use Magento\Framework\App\Filesystem\Directory_List as AppDirectoryList;
use Magento\Framework\App\Object_Manager\Environment;
use Magento\Framework\Cache\Frontend\Decorator\Profiler as ProfilerDecorator;
use Magento\Framework\Code\Generated_Files;
use Magento\Framework\Code\Generator;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Config\File_Iterator_Factory;
use Magento\Framework\Data\Argument\Interpreter\Array_Type;
use Magento\Framework\Data\Argument\Interpreter\Base_String_Utils;
use Magento\Framework\Data\Argument\Interpreter\Boolean;
use Magento\Framework\Data\Argument\Interpreter\Composite;
use Magento\Framework\Data\Argument\Interpreter\Constant;
use Magento\Framework\Data\Argument\Interpreter\Data_Object;
use Magento\Framework\Data\Argument\Interpreter\Null_Type;
use Magento\Framework\Data\Argument\Interpreter\Number;
use Magento\Framework\Data\Argument\Interpreter_Interface;
use Magento\Framework\Exception\State\Init_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read_Factory;
use Magento\Framework\Filesystem\Directory\Write_Factory;
use Magento\Framework\Filesystem\Directory_List;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Filesystem\Driver_Pool;
use Magento\Framework\Filesystem\File\Read_Factory as FileReadFactory;
use Magento\Framework\Interception\Definition_Interface as InterceptionDefinitionInterface;
use Magento\Framework\Interception\Object_Manager\Config_Interface;
use Magento\Framework\Interception\Plugin_List\Plugin_List;
use Magento\Framework\Lock\Backend\File_Lock;
use Magento\Framework\Object_Manager\Config\Config as DiConfig;
use Magento\Framework\Object_Manager\Config\Mapper\Dom as DomMapper;
use Magento\Framework\Object_Manager\Config\Reader\Dom as DomReader;
use Magento\Framework\Object_Manager\Config\Schema_Locator;
use Magento\Framework\Object_Manager\Config_Interface as ObjectManagerConfigInterface;
use Magento\Framework\Object_Manager\Config_Loader_Interface;
use Magento\Framework\Object_Manager\Definition_Factory;
use Magento\Framework\Object_Manager\Definition_Interface;
use Magento\Framework\Object_Manager\Factory_Interface;
use Magento\Framework\Object_Manager\Relations_Interface;
use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Phrase;
use Magento\Framework\Profiler;
use Magento\Framework\Stdlib\Boolean_Utils;
/**
 * Initialization of object manager is a complex operation.
 * To abstract away this complexity, this class was introduced.
 * Objects of this class create fully initialized instance of object manager with "global" configuration loaded.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Object_Manager_Factory
{
    /**
     * Initialization parameter for a custom deployment configuration file
     */
    public const INIT_PARAM_DEPLOYMENT_CONFIG_FILE = 'MAGE_CONFIG_FILE';
    /**
     * Initialization parameter for custom deployment configuration data
     */
    public const INIT_PARAM_DEPLOYMENT_CONFIG = 'MAGE_CONFIG';
    /**
     * Object manager class name for locating services
     *
     * @var string
     */
    protected $_locator_class_name = Object_Manager::class;
    /**
     * Interception configuration class name
     *
     * @var string
     */
    protected $_config_class_name = Config_Interface::class;
    /**
     * Environment factory class name
     *
     * @var string
     */
    protected $env_factory_class_name = Environment_Factory::class;
    /**
     * Filesystem directory list
     *
     * @var AppDirectoryList
     */
    protected $directory_list;
    /**
     * Filesystem driver pool
     *
     * @var DriverPool
     */
    protected $driver_pool;
    /**
     * Configuration file pool
     *
     * @var ConfigFilePool
     */
    protected $config_file_pool;
    /**
     * Object manager factory instance
     *
     * @var FactoryInterface
     */
    protected $factory;
    /**
     * Constructor
     *
     * @param AppDirectoryList $directoryList
     * @param DriverPool $driverPool
     * @param ConfigFilePool $configFilePool
     */
    public function __construct(App_Directory_List $directory_list, Driver_Pool $driver_pool, Config_File_Pool $config_file_pool)
    {
        $this->directory_list = $directory_list;
        $this->driver_pool = $driver_pool;
        $this->config_file_pool = $config_file_pool;
    }
    /**
     * Create ObjectManager
     *
     * @param array $arguments
     * @return ObjectManagerInterface
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function create(array $arguments)
    {
        $write_factory = new Write_Factory($this->driver_pool);
        /** @var FileDriver $fileDriver */
        $file_driver = $this->driver_pool->get_driver(Driver_Pool::FILE);
        $lock_manager = new File_Lock($file_driver, $this->directory_list->get_root());
        $generated_files = new Generated_Files($this->directory_list, $write_factory, $lock_manager);
        $generated_files->clean_generated_files();
        $deployment_config = $this->create_deployment_config($this->directory_list, $this->config_file_pool, $arguments);
        $arguments = array_merge($deployment_config->get(), $arguments);
        $definition_factory = new Definition_Factory($this->driver_pool->get_driver(Driver_Pool::FILE), $this->directory_list->get_path(App_Directory_List::GENERATED_CODE));
        $definitions = $definition_factory->create_class_definition();
        $relations = $definition_factory->create_relations();
        /** @var EnvironmentFactory $envFactory */
        $env_factory = new $this->env_factory_class_name($relations, $definitions);
        /** @var EnvironmentInterface $env */
        $env = $env_factory->create_environment();
        /** @var ConfigInterface $diConfig */
        $di_config = $env->get_di_config();
        $app_mode = isset($arguments[State::PARAM_MODE]) ? $arguments[State::PARAM_MODE] : State::MODE_DEFAULT;
        $boolean_utils = new Boolean_Utils();
        $arg_interpreter = $this->create_argument_interpreter($boolean_utils);
        $argument_mapper = new Dom_Mapper($arg_interpreter);
        if ($env->get_mode() != Environment\Compiled::MODE) {
            $config_data = $this->_load_primary_config($this->directory_list, $this->driver_pool, $argument_mapper, $app_mode);
            if ($config_data) {
                $di_config->extend($config_data);
            }
        }
        // set cache profiler decorator if enabled
        if (Profiler::is_enabled()) {
            $cache_factory_arguments = $di_config->get_arguments(Cache_Frontend_Factory::class);
            $cache_factory_arguments['decorators'][] = ['class' => Profiler_Decorator::class, 'parameters' => ['backendPrefixes' => ['Magento\Framework\Cache\Backend\\', 'Magento\Framework\Cache\Frontend\Adapter\Symfony\\', 'Cm_Cache_Backend_']]];
            $cache_factory_config = [Cache_Frontend_Factory::class => ['arguments' => $cache_factory_arguments]];
            $di_config->extend($cache_factory_config);
        }
        $shared_instances = [Deployment_Config::class => $deployment_config, App_Directory_List::class => $this->directory_list, Directory_List::class => $this->directory_list, Driver_Pool::class => $this->driver_pool, Relations_Interface::class => $relations, Interception_Definition_Interface::class => $definition_factory->create_plugin_definition(), Object_Manager_Config_Interface::class => $di_config, Config_Interface::class => $di_config, Definition_Interface::class => $definitions, Boolean_Utils::class => $boolean_utils, Dom_Mapper::class => $argument_mapper, Config_Loader_Interface::class => $env->get_object_manager_config_loader(), $this->_config_class_name => $di_config];
        $arguments['shared_instances'] =& $shared_instances;
        $this->factory = $env->get_object_manager_factory($arguments);
        /** @var ObjectManagerInterface $objectManager */
        $object_manager = new $this->_locator_class_name($this->factory, $di_config, $shared_instances);
        $this->factory->set_object_manager($object_manager);
        $generator_params = $di_config->get_arguments(Generator::class);
        /** Arguments are stored in different format when DI config is compiled, thus require custom processing */
        $generated_entities = isset($generator_params['generatedEntities']['_v_']) ? $generator_params['generatedEntities']['_v_'] : (isset($generator_params['generatedEntities']) ? $generator_params['generatedEntities'] : []);
        $definition_factory->get_code_generator()->set_object_manager($object_manager)->set_generated_entities($generated_entities);
        $env->configure_object_manager($di_config, $shared_instances);
        return $object_manager;
    }
    /**
     * Creates deployment configuration object
     *
     * @param AppDirectoryList $directoryList
     * @param ConfigFilePool $configFilePool
     * @param array $arguments
     * @return DeploymentConfig
     */
    protected function create_deployment_config(App_Directory_List $directory_list, Config_File_Pool $config_file_pool, array $arguments)
    {
        $custom_file = isset($arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG_FILE]) ? $arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG_FILE] : null;
        $custom_data = isset($arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG]) ? $arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG] : [];
        $reader = new Deployment_Config\Reader($directory_list, $this->driver_pool, $config_file_pool, $custom_file);
        return new Deployment_Config($reader, $custom_data);
    }
    /**
     * Return newly created instance on an argument interpreter, suitable for processing DI arguments
     *
     * @param BooleanUtils $booleanUtils
     * @return InterpreterInterface
     */
    protected function create_argument_interpreter(Boolean_Utils $boolean_utils)
    {
        $const_interpreter = new Constant();
        $result = new Composite(['boolean' => new Boolean($boolean_utils), 'string' => new Base_String_Utils($boolean_utils), 'number' => new Number(), 'null' => new Null_Type(), 'object' => new Data_Object($boolean_utils), 'const' => $const_interpreter, 'init_parameter' => new Argument_Interpreter($const_interpreter)], Dom_Reader::TYPE_ATTRIBUTE);
        // Add interpreters that reference the composite
        $result->add_interpreter('array', new Array_Type($result));
        return $result;
    }
    /**
     * Load primary config
     *
     * @param DirectoryList $directoryList
     * @param DriverPool $driverPool
     * @param mixed $argumentMapper
     * @param string $appMode
     * @return array
     * @throws InitException
     */
    protected function _load_primary_config(Directory_List $directory_list, $driver_pool, $argument_mapper, $app_mode)
    {
        $config_data = null;
        try {
            $file_resolver = new Primary(new Filesystem($directory_list, new Read_Factory($driver_pool), new Write_Factory($driver_pool)), new File_Iterator_Factory(new File_Read_Factory($driver_pool)));
            $schema_locator = new Schema_Locator();
            $validation_state = new Validation_State($app_mode);
            $reader = new Dom_Reader($file_resolver, $argument_mapper, $schema_locator, $validation_state);
            $config_data = $reader->read('primary');
        } catch (\Exception $e) {
            throw new Init_Exception(new Phrase($e->get_message()), $e);
        }
        return $config_data;
    }
    /**
     * Crete plugin list object
     *
     * @param ObjectManagerInterface $objectManager
     * @param RelationsInterface $relations
     * @param DefinitionFactory $definitionFactory
     * @param DiConfig $diConfig
     * @param DefinitionInterface $definitions
     * @return PluginList
     * @deprecated 101.0.0 Use ObjectManager::create() directly instead
     * @see ObjectManagerInterface::create()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _create_plugin_list(Object_Manager_Interface $object_manager, Relations_Interface $relations, Definition_Factory $definition_factory, Di_Config $di_config, Definition_Interface $definitions)
    {
        return $object_manager->create(Plugin_List::class, ['relations' => $relations, 'definitions' => $definition_factory->create_plugin_definition(), 'omConfig' => $di_config, 'classDefinitions' => null]);
    }
}