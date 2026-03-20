<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App_Interface;
use Magento\Framework\Autoload\Autoloader_Registry;
use Magento\Framework\Autoload\Populator;
use Magento\Framework\Config\File\Config_File_Pool;
use Magento\Framework\Filesystem\Driver_Pool;
use Magento\Framework\HTTP\Php_Environment\Response;
use Psr\Log\Logger_Interface;
/**
 * A bootstrap of Magento application
 *
 * Performs basic initialization root function: injects init parameters and creates object manager
 * Can create/run applications
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Bootstrap
{
    /**#@+
     * Possible errors that can be triggered by the bootstrap
     */
    public const ERR_MAINTENANCE = 901;
    public const ERR_IS_INSTALLED = 902;
    /**#@- */
    /**#@+
     * Initialization parameters that allow control bootstrap behavior of asserting maintenance mode or is installed
     *
     * Possible values:
     * - true -- set expectation that it is required
     * - false -- set expectation that is required not to
     * - null -- bypass the assertion completely
     *
     * If key is absent in the parameters array, the default behavior will be used
     * @see DEFAULT_REQUIRE_MAINTENANCE
     * @see DEFAULT_REQUIRE_IS_INSTALLED
     */
    public const PARAM_REQUIRE_MAINTENANCE = 'MAGE_REQUIRE_MAINTENANCE';
    public const PARAM_REQUIRE_IS_INSTALLED = 'MAGE_REQUIRE_IS_INSTALLED';
    /**#@- */
    /**#@+
     * Default behavior of bootstrap assertions
     */
    public const DEFAULT_REQUIRE_MAINTENANCE = false;
    public const DEFAULT_REQUIRE_IS_INSTALLED = true;
    /**#@- */
    /**
     * Initialization parameter for custom directory paths
     */
    public const INIT_PARAM_FILESYSTEM_DIR_PATHS = 'MAGE_DIRS';
    /**
     * Initialization parameter for additional filesystem drivers
     */
    public const INIT_PARAM_FILESYSTEM_DRIVERS = 'MAGE_FILESYSTEM_DRIVERS';
    /**
     * The initialization parameters (normally come from the $_SERVER)
     *
     * @var array
     */
    private $server;
    /**
     * Root directory
     *
     * @var string
     */
    private $root_dir;
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $object_manager;
    /**
     * Maintenance mode manager
     *
     * @var \Magento\Framework\App\MaintenanceMode
     */
    private $maintenance;
    /**
     * Bootstrap-specific error code that may have been set in runtime
     *
     * @var int
     */
    private $error_code = 0;
    /**
     * Attribute for creating object manager
     *
     * @var ObjectManagerFactory
     */
    private $factory;
    /**
     * Static method so that client code does not have to create Object Manager Factory every time Bootstrap is called
     *
     * @param string $rootDir
     * @param array $initParams
     * @param ObjectManagerFactory $factory
     * @return Bootstrap
     */
    public static function create($root_dir, array $init_params, ?Object_Manager_Factory $factory = null)
    {
        self::populate_autoloader($root_dir, $init_params);
        if ($factory === null) {
            $factory = self::create_object_manager_factory($root_dir, $init_params);
        }
        return new self($factory, $root_dir, $init_params);
    }
    /**
     * Populates autoloader with mapping info
     *
     * @param string $rootDir
     * @param array $initParams
     * @return void
     */
    public static function populate_autoloader($root_dir, $init_params)
    {
        $dir_list = self::create_filesystem_directory_list($root_dir, $init_params);
        $autoload_wrapper = Autoloader_Registry::get_autoloader();
        Populator::populate_mappings($autoload_wrapper, $dir_list);
    }
    /**
     * Creates instance of object manager factory
     *
     * @param string $rootDir
     * @param array $initParams
     * @return ObjectManagerFactory
     */
    public static function create_object_manager_factory($root_dir, array $init_params)
    {
        $dir_list = self::create_filesystem_directory_list($root_dir, $init_params);
        $driver_pool = self::create_filesystem_driver_pool($init_params);
        $config_file_pool = self::create_config_file_pool();
        return new Object_Manager_Factory($dir_list, $driver_pool, $config_file_pool);
    }
    /**
     * Creates instance of filesystem directory list
     *
     * @param string $rootDir
     * @param array $initParams
     * @return DirectoryList
     */
    public static function create_filesystem_directory_list($root_dir, array $init_params)
    {
        $custom_dirs = [];
        if (isset($init_params[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS])) {
            $custom_dirs = $init_params[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        }
        return new Directory_List($root_dir, $custom_dirs);
    }
    /**
     * Creates instance of filesystem driver pool
     *
     * @param array $initParams
     * @return DriverPool
     */
    public static function create_filesystem_driver_pool(array $init_params)
    {
        $extra_drivers = [];
        if (isset($init_params[Bootstrap::INIT_PARAM_FILESYSTEM_DRIVERS])) {
            $extra_drivers = $init_params[Bootstrap::INIT_PARAM_FILESYSTEM_DRIVERS];
        }
        return new Driver_Pool($extra_drivers);
    }
    /**
     * Creates instance of configuration files pool
     *
     * @return DriverPool
     */
    public static function create_config_file_pool()
    {
        return new Config_File_Pool();
    }
    /**
     * Constructor
     *
     * @param ObjectManagerFactory $factory
     * @param string $rootDir
     * @param array $initParams
     */
    public function __construct(Object_Manager_Factory $factory, $root_dir, array $init_params)
    {
        $this->factory = $factory;
        $this->root_dir = $root_dir;
        $this->server = $init_params;
        $this->object_manager = $this->factory->create($this->server);
    }
    /**
     * Gets the current parameters
     *
     * @return array
     */
    public function get_params()
    {
        return $this->server;
    }
    /**
     * Factory method for creating application instances
     *
     * In case of failure,
     * the application will be terminated by "exit(1)"
     *
     * @param string $type
     * @param array $arguments
     * @return \Magento\Framework\AppInterface | void
     */
    public function create_application($type, $arguments = [])
    {
        try {
            $application = $this->object_manager->create($type, $arguments);
            if (!$application instanceof App_Interface) {
                throw new \InvalidArgumentException("The provided class doesn't implement AppInterface: {$type}");
            }
            return $application;
        } catch (\Exception $e) {
            $this->terminate($e);
        }
    }
    /**
     * Runs an application
     *
     * @param \Magento\Framework\AppInterface $application
     * @return void
     *
     * phpcs:disable Magento2.Exceptions,Squiz.Commenting.FunctionCommentThrowTag
     */
    public function run(App_Interface $application)
    {
        try {
            try {
                \Magento\Framework\Profiler::start('magento');
                $this->init_error_handler();
                $this->assert_maintenance();
                $this->assert_installed();
                $response = $application->launch();
                $response->send_response();
                \Magento\Framework\Profiler::stop('magento');
            } catch (\Exception $e) {
                \Magento\Framework\Profiler::stop('magento');
                $this->object_manager->get(Logger_Interface::class)->error($e->get_message());
                if (!$application->catch_exception($this, $e)) {
                    throw $e;
                }
            }
        } catch (\Throwable $e) {
            $this->terminate($e);
        }
    }
    // phpcs:enable
    /**
     * Asserts maintenance mode
     *
     * @return void
     * @throws \Exception
     *
     * phpcs:disable Magento2.Exceptions
     */
    protected function assert_maintenance()
    {
        $is_expected = $this->get_is_expected(self::PARAM_REQUIRE_MAINTENANCE, self::DEFAULT_REQUIRE_MAINTENANCE);
        if (null === $is_expected) {
            return;
        }
        /** @var \Magento\Framework\App\MaintenanceMode $maintenance */
        $this->maintenance = $this->object_manager->get(\Magento\Framework\App\Maintenance_Mode::class);
        /** @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $phpRemoteAddressEnvironment */
        $php_remote_address_environment = $this->object_manager->get(\Magento\Framework\HTTP\Php_Environment\Remote_Address::class);
        $remote_address = $php_remote_address_environment->get_remote_address();
        $is_on = $this->maintenance->is_on($remote_address ? $remote_address : '');
        if ($is_on && !$is_expected) {
            $this->error_code = self::ERR_MAINTENANCE;
            throw new \Exception('Unable to proceed: the maintenance mode is enabled. ');
        }
        if (!$is_on && $is_expected) {
            $this->error_code = self::ERR_MAINTENANCE;
            throw new \Exception('Unable to proceed: the maintenance mode must be enabled first. ');
        }
    }
    // phpcs:enable
    /**
     * Asserts whether application is installed
     *
     * @return void
     * @throws \Exception
     */
    protected function assert_installed()
    {
        $is_expected = $this->get_is_expected(self::PARAM_REQUIRE_IS_INSTALLED, self::DEFAULT_REQUIRE_IS_INSTALLED);
        if (null === $is_expected) {
            return;
        }
        $is_installed = $this->is_installed();
        if (!$is_installed && $is_expected) {
            $this->error_code = self::ERR_IS_INSTALLED;
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Error: Application is not installed yet. ');
        }
        if ($is_installed && !$is_expected) {
            $this->error_code = self::ERR_IS_INSTALLED;
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Error: Application is already installed. ');
        }
    }
    /**
     * Analyze a key in the initialization parameters as "is expected" parameter
     *
     * If there is no such key, returns default value. Otherwise casts it to boolean, unless it is null
     *
     * @param string $key
     * @param bool $default
     * @return bool|null
     */
    private function get_is_expected($key, $default)
    {
        if (array_key_exists($key, $this->server)) {
            if (isset($this->server[$key])) {
                return (bool) (int) $this->server[$key];
            }
            return null;
        }
        return $default;
    }
    /**
     * Determines whether application is installed
     *
     * @return bool
     */
    private function is_installed()
    {
        /** @var \Magento\Framework\App\DeploymentConfig $deploymentConfig */
        $deployment_config = $this->object_manager->get(\Magento\Framework\App\Deployment_Config::class);
        return $deployment_config->is_available();
    }
    /**
     * Gets the object manager instance
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function get_object_manager()
    {
        return $this->object_manager;
    }
    /**
     * Sets a custom error handler
     *
     * @return void
     */
    private function init_error_handler()
    {
        $handler = new Error_Handler();
        set_error_handler([$handler, 'handler']);
    }
    /**
     * Getter for error code
     *
     * @return int
     */
    public function get_error_code()
    {
        return $this->error_code;
    }
    /**
     * Checks whether developer mode is set in the initialization parameters
     *
     * @return bool
     */
    public function is_developer_mode()
    {
        $mode = 'default';
        if (isset($this->server[State::PARAM_MODE])) {
            $mode = $this->server[State::PARAM_MODE];
        } else {
            $deployment_config = $this->get_object_manager()->get(Deployment_Config::class);
            $config_mode = $deployment_config->get(State::PARAM_MODE);
            if ($config_mode) {
                $mode = $config_mode;
            }
        }
        return $mode == State::MODE_DEVELOPER;
    }
    /**
     * Display an exception and terminate program execution
     *
     * @param \Throwable $e
     * @return void
     *
     * phpcs:disable Magento2.Security.LanguageConstruct, Squiz.Commenting.FunctionCommentThrowTag
     */
    protected function terminate(\Throwable $e)
    {
        /** @var Response $response */
        $response = $this->object_manager->get(Response::class);
        $response->clear_headers();
        $response->set_http_response_code(500);
        $response->set_header('Content-Type', 'text/plain');
        if ($this->is_developer_mode()) {
            $response->set_body($e);
        } else {
            $message = "An error has happened during application run. See exception log for details.\n";
            try {
                if (!$this->object_manager) {
                    throw new \DomainException();
                }
                $this->object_manager->get(Logger_Interface::class)->critical($e);
            } catch (\Exception $e) {
                $message .= "Could not write error message to log. Please use developer mode to see the message.\n";
            }
            $response->set_body($message);
        }
        $response->send_response();
        exit(1);
    }
    // phpcs:enable
}