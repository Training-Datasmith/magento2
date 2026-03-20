<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Console;

use Laminas\Service_Manager\Service_Manager;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Product_Metadata;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Composer\Composer_Json_Finder;
use Magento\Framework\Console\Command_Loader\Aggregate;
use Magento\Framework\Console\Exception\Generation_Directory_Access_Exception;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Shell\Complex_Parameter;
use Magento\Setup\Application;
use Magento\Setup\Console\Command_Loader as SetupCommandLoader;
use Magento\Setup\Console\Compiler_Preparation;
use Magento\Setup\Model\Object_Manager_Provider;
use Psr\Log\Logger_Interface;
use Symfony\Component\Console;
/**
 * Magento 2 CLI Application.
 *
 * This is the hood for all command line tools supported by Magento.
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cli extends Console\Application
{
    /**
     * Name of input option.
     */
    public const INPUT_KEY_BOOTSTRAP = 'bootstrap';
    /**#@+
     * Cli exit codes.
     */
    public const RETURN_SUCCESS = 0;
    public const RETURN_FAILURE = 1;
    /**#@-*/
    /**
     * @var ServiceManager
     */
    private $service_manager;
    /**
     * Initialization exception.
     *
     * @var \Exception
     */
    private $init_exception;
    /**
     * Exception that occurred during command initialization.
     *
     * @var \Exception
     */
    private $get_commands_exception;
    /**
     * Failed commands during loading
     *
     * @var array
     */
    private $failed_commands = [];
    /**
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @param string $name the application name
     * @param string $version the application version
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        try {
            // phpcs:ignore Magento2.Security.IncludeFile
            $configuration = require BP . '/setup/config/application.config.php';
            $bootstrap_application = new Application();
            $application = $bootstrap_application->bootstrap($configuration);
            $this->service_manager = $application->get_service_manager();
            $this->assert_compiler_preparation();
            $this->init_object_manager();
        } catch (\Exception $exception) {
            $output = new \Symfony\Component\Console\Output\Console_Output();
            $output->writeln('<error>' . $exception->get_message() . '</error>');
            // phpcs:disable
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            exit(static::RETURN_FAILURE);
            // phpcs:enable
        }
        if ($version == 'UNKNOWN') {
            $directory_list = new Directory_List(BP);
            $composer_json_finder = new Composer_Json_Finder($directory_list);
            $product_metadata = new Product_Metadata($composer_json_finder);
            $version = $product_metadata->get_version();
        }
        parent::__construct($name, $version);
        $this->service_manager->set_service(\Symfony\Component\Console\Application::class, $this);
        $this->logger = $this->object_manager->get(Logger_Interface::class);
        $this->set_command_loader($this->get_command_loader());
    }
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Exception The exception in case of unexpected error
     */
    public function do_run(Console\Input\Input_Interface $input, Console\Output\Output_Interface $output): int
    {
        $exit_code = null;
        try {
            $exit_code = parent::do_run($input, $output);
        } catch (\Exception $e) {
            $error_message = $e->get_message() . PHP_EOL . $e->get_trace_as_string();
            if ($this->get_commands_exception) {
                // Command loading exception occurred earlier
                if ($this->is_developer_mode()) {
                    // Developer mode: provide detailed error about command loading failure
                    $combined_error_message = 'Exception during console commands initialization: ' . $this->get_commands_exception->get_message() . PHP_EOL;
                    $this->init_exception = new \Exception($combined_error_message, $e->get_code(), $e);
                    try {
                        if ($this->logger) {
                            $this->logger->error($combined_error_message);
                        }
                    } catch (\Exception $log_ex) {
                        error_log($combined_error_message);
                    }
                } else {
                    // Production mode: command loading errors were already logged
                    // The specific command user tried to run may not be available
                    $warning_message = PHP_EOL . '<comment>Warning: Some commands failed to load due to errors.</comment>' . PHP_EOL . '<comment>Check error logs (var/log/system.log) for details.</comment>' . PHP_EOL . '<comment>The command you tried to run may not be available. ' . 'Try running: bin/magento list</comment>' . PHP_EOL;
                    try {
                        $output->writeln($warning_message);
                    } catch (\Exception $output_ex) {
                        error_log(strip_tags($warning_message));
                    }
                    // Return failure since the command couldn't be executed
                    return self::RETURN_FAILURE;
                }
            } else {
                // Not a command loading exception, handle normally
                $this->init_exception = $e;
                try {
                    if ($this->logger) {
                        $this->logger->error($error_message);
                    }
                } catch (\Exception $log_ex) {
                    error_log($error_message);
                }
            }
        }
        if ($this->init_exception) {
            throw $this->init_exception;
        }
        return $exit_code !== null ? $exit_code : self::RETURN_SUCCESS;
    }
    /**
     * @inheritdoc
     */
    protected function get_default_commands(): array
    {
        return array_merge(parent::get_default_commands(), $this->get_application_commands());
    }
    /**
     * Gets application commands.
     *
     * @return array a list of available application commands
     */
    protected function get_application_commands()
    {
        $commands = [];
        // Load core Magento commands (try-catch to continue if this fails)
        try {
            if ($this->object_manager->get(Deployment_Config::class)->is_available()) {
                /** @var CommandListInterface */
                $command_list = $this->object_manager->create(Command_List_Interface::class);
                $commands = array_merge($commands, $command_list->get_commands());
            }
        } catch (\Exception $e) {
            $this->handle_command_loading_exception('core Magento commands', $e);
        }
        // Load vendor commands (already has its own try-catch internally)
        try {
            $commands = array_merge($commands, $this->get_vendor_commands($this->object_manager));
        } catch (\Exception $e) {
            $this->handle_command_loading_exception('vendor commands', $e);
        }
        return $commands;
    }
    /**
     * Handle exception during command loading
     *
     * @param string $commandType
     * @param \Exception $e
     * @return void
     */
    private function handle_command_loading_exception(string $command_type, \Exception $e): void
    {
        // Store exception
        $this->get_commands_exception = $e;
        // Developer mode: fail immediately with clear exception
        if ($this->is_developer_mode()) {
            $this->init_exception = $e;
            throw $e;
        }
        // Production mode: log detailed error and continue
        $error_message = sprintf('Failed to load %s: %s in %s:%d', $command_type, $e->get_message(), $e->get_file(), $e->get_line());
        // Special handling for core commands failure - this is critical
        if ($command_type === 'core Magento commands') {
            $error_message .= PHP_EOL . PHP_EOL . 'CRITICAL: Core Magento commands ' . '(cache:flush, deploy:mode:set, etc.) are unavailable!';
            $error_message .= PHP_EOL . 'This usually happens when a custom module ' . 'injects a broken command into di.xml.';
            $error_message .= PHP_EOL . PHP_EOL . 'TO FIX THIS IMMEDIATELY:';
            $error_message .= PHP_EOL . '1. Run: rm -rf generated/code var/cache var/page_cache';
            $error_message .= PHP_EOL . '2. If problem persists, check var/log/system.log for the broken class';
            $error_message .= PHP_EOL . '3. Disable the problematic module or fix the command class';
        }
        // Ensure the error is logged to both Magento logs and PHP error log
        $this->log_command_loading_error($error_message, $e);
    }
    /**
     * Object Manager initialization.
     *
     * @return void
     */
    private function init_object_manager()
    {
        $params = (new Complex_Parameter(self::INPUT_KEY_BOOTSTRAP))->merge_from_argv($_SERVER, $_SERVER);
        $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;
        $request_params = $this->service_manager->get('magento-init-params');
        $app_bootstrap_keys = [Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS, App_State::PARAM_MODE];
        foreach ($app_bootstrap_keys as $app_bootstrap_key) {
            if (isset($request_params[$app_bootstrap_key]) && !isset($params[$app_bootstrap_key])) {
                $params[$app_bootstrap_key] = $request_params[$app_bootstrap_key];
            }
        }
        $this->object_manager = Bootstrap::create(BP, $params)->get_object_manager();
        /** @var ObjectManagerProvider $omProvider */
        $om_provider = $this->service_manager->get(Object_Manager_Provider::class);
        $om_provider->set_object_manager($this->object_manager);
    }
    /**
     * Checks whether compiler is being prepared.
     *
     * @return void
     * @throws GenerationDirectoryAccessException If generation directory is read-only
     */
    private function assert_compiler_preparation()
    {
        /**
         * Temporary workaround until the compiler is able to clear the generation directory
         * @todo remove after MAGETWO-44493 resolved
         */
        if (class_exists(Compiler_Preparation::class)) {
            $compiler_preparation = new Compiler_Preparation($this->service_manager, new Console\Input\Argv_Input(), new File());
            $compiler_preparation->handle_compiler_environment();
        }
    }
    /**
     * Retrieves vendor commands.
     *
     * @param ObjectManagerInterface $objectManager the object manager
     *
     * @return array an array with external commands
     */
    protected function get_vendor_commands($object_manager)
    {
        $commands = [];
        $this->failed_commands = [];
        // Reset for each call
        foreach (Command_Locator::get_commands() as $command_list_class) {
            if (!class_exists($command_list_class)) {
                continue;
            }
            try {
                $command_list = $object_manager->create($command_list_class);
                $commands[] = $command_list->get_commands();
            } catch (\Exception $e) {
                // Store failure information
                $this->failed_commands[] = ['class' => $command_list_class, 'error' => $e->get_message(), 'type' => get_class($e), 'file' => $e->get_file() . ':' . $e->get_line()];
                // Log the error
                $error_message = sprintf('Failed to load command class %s: %s', $command_list_class, $e->get_message());
                $this->logger->error($error_message, ['exception' => $e, 'trace' => $e->get_trace_as_string()]);
                // Developer mode: fail immediately with clear exception
                if ($this->is_developer_mode()) {
                    throw $e;
                }
                // Production mode: continue loading other commands
            }
        }
        // Log summary in production mode if there were failures
        if (!empty($this->failed_commands) && !$this->is_developer_mode()) {
            $this->log_failed_commands_summary();
        }
        return array_merge([], ...$commands);
    }
    /**
     * Generate and return the Command Loader
     *
     * @throws \LogicException
     * @throws \BadMethodCallException
     */
    private function get_command_loader(): Console\Command_Loader\Command_Loader_Interface
    {
        $command_loaders = [];
        if (class_exists(Setup_Command_Loader::class)) {
            $command_loaders[] = new Setup_Command_Loader($this->service_manager);
        }
        $command_loaders[] = $this->object_manager->create(Command_Loader::class);
        return $this->object_manager->create(Aggregate::class, ['commandLoaders' => $command_loaders]);
    }
    /**
     * Check if application is running in developer mode.
     *
     * @return bool
     */
    private function is_developer_mode(): bool
    {
        try {
            // Check via env.php MAGE_MODE setting first
            if ($this->object_manager) {
                $deployment_config = $this->object_manager->get(Deployment_Config::class);
                $mode = $deployment_config->get(App_State::PARAM_MODE);
                if ($mode && $mode !== App_State::MODE_DEVELOPER) {
                    return false;
                }
                // Also check AppState as fallback
                try {
                    /** @var AppState $appState */
                    $app_state = $this->object_manager->get(App_State::class);
                    return $app_state->get_mode() === App_State::MODE_DEVELOPER;
                } catch (\Exception $e) {
                    // Fallback to deployment config value
                    return $mode === App_State::MODE_DEVELOPER;
                }
            }
            // Default to production (safer)
            return false;
        } catch (\Exception $e) {
            // If we can't determine the mode, assume production (safer option)
            return false;
        }
    }
    /**
     * Log a summary of all failed commands.
     *
     * @return void
     */
    private function log_failed_commands_summary(): void
    {
        if (empty($this->failed_commands)) {
            return;
        }
        $summary = sprintf('Failed to load %d command class(es). The CLI will continue with available commands:' . PHP_EOL, count($this->failed_commands));
        foreach ($this->failed_commands as $failure) {
            $summary .= sprintf('  - %s: %s (%s at %s)' . PHP_EOL, $failure['class'], $failure['error'], $failure['type'], $failure['file']);
        }
        $this->logger->warning($summary);
    }
    /**
     * Log command loading error to both Magento logs and PHP error log.
     *
     * @param string $errorMessage
     * @param \Exception $exception
     * @return void
     */
    private function log_command_loading_error(string $error_message, \Exception $exception): void
    {
        // Try to log to Magento's log system
        $logged_to_magento = false;
        if ($this->logger) {
            try {
                $this->logger->error($error_message, ['exception' => $exception, 'trace' => $exception->get_trace_as_string()]);
                $logged_to_magento = true;
                // Also log a warning that CLI will continue
                $this->logger->warning('Some commands failed to load. The CLI will continue with available commands. ' . 'Check system.log for details.');
            } catch (\Exception $log_exception) {
                // Logger failed, will show in terminal
                $logged_to_magento = false;
            }
        }
        // Show actionable error in terminal (production mode)
        $terminal_message = PHP_EOL . str_repeat('=', 80) . PHP_EOL;
        $terminal_message .= '  MAGENTO CLI ERROR (Production Mode)' . PHP_EOL;
        $terminal_message .= str_repeat('=', 80) . PHP_EOL;
        // Extract just the first line of error
        $error_lines = explode(PHP_EOL, $error_message);
        $terminal_message .= $error_lines[0] . PHP_EOL;
        // Check if this is a critical core commands failure
        if (strpos($error_message, 'CRITICAL: Core Magento commands') !== false) {
            $terminal_message .= PHP_EOL . ' CRITICAL: Commands like cache:flush, deploy:mode:set are UNAVAILABLE!' . PHP_EOL;
            $terminal_message .= PHP_EOL . 'Try running the following command to see the available commands:' . PHP_EOL;
            $terminal_message .= '  bin/magento list' . PHP_EOL;
        }
        if ($logged_to_magento) {
            $terminal_message .= PHP_EOL . ' Full details logged to: var/log/system.log' . PHP_EOL;
        } else {
            $terminal_message .= PHP_EOL . ' Full error details shown above' . PHP_EOL;
        }
        $terminal_message .= str_repeat('=', 80) . PHP_EOL;
        error_log($terminal_message);
    }
}