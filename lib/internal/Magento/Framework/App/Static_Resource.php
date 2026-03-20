<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Config\Config_Options_List_Constants;
use Magento\Framework\Debug;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Object_Manager\Config_Loader_Interface;
use Magento\Framework\Validator\Locale;
use Magento\Framework\View\Design\Theme\Theme_Package_List;
use Psr\Log\Logger_Interface;
/**
 * Entry point for retrieving static resources like JS, CSS, images by requested public path
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Static_Resource implements \Magento\Framework\App_Interface
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \Magento\Framework\App\Response\FileInterface
     */
    private $response;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var \Magento\Framework\App\View\Asset\Publisher
     */
    private $publisher;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $asset_repo;
    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    private $module_list;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $object_manager;
    /**
     * @var \Magento\Framework\ObjectManager\ConfigLoaderInterface
     */
    private $config_loader;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var DeploymentConfig
     */
    private $deployment_config;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var File
     */
    private $driver;
    /**
     * @var ThemePackageList
     */
    private $theme_package_list;
    /**
     * @var Locale
     */
    private $locale_validator;
    /**
     * @param State $state
     * @param Response\FileInterface $response
     * @param Request\Http $request
     * @param View\Asset\Publisher $publisher
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigLoaderInterface $configLoader
     * @param DeploymentConfig|null $deploymentConfig
     * @param File|null $driver
     * @param ThemePackageList|null $themePackageList
     * @param Locale|null $localeValidator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(State $state, Response\File_Interface $response, Request\Http $request, View\Asset\Publisher $publisher, \Magento\Framework\View\Asset\Repository $asset_repo, \Magento\Framework\Module\Module_List $module_list, \Magento\Framework\Object_Manager_Interface $object_manager, Config_Loader_Interface $config_loader, ?Deployment_Config $deployment_config = null, ?File $driver = null, ?Theme_Package_List $theme_package_list = null, ?Locale $locale_validator = null)
    {
        $this->state = $state;
        $this->response = $response;
        $this->request = $request;
        $this->publisher = $publisher;
        $this->asset_repo = $asset_repo;
        $this->module_list = $module_list;
        $this->object_manager = $object_manager;
        $this->config_loader = $config_loader;
        $this->deployment_config = $deployment_config ?: Object_Manager::get_instance()->get(Deployment_Config::class);
        $this->driver = $driver ?: Object_Manager::get_instance()->get(File::class);
        $this->theme_package_list = $theme_package_list ?? Object_Manager::get_instance()->get(Theme_Package_List::class);
        $this->locale_validator = $locale_validator ?? Object_Manager::get_instance()->get(Locale::class);
    }
    /**
     * Finds requested resource and provides it to the client
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function launch()
    {
        // disabling profiling when retrieving static resource
        \Magento\Framework\Profiler::reset();
        $app_mode = $this->state->get_mode();
        if ($app_mode == \Magento\Framework\App\State::MODE_PRODUCTION && !$this->deployment_config->get_config_data(Config_Options_List_Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)) {
            $this->response->set_http_response_code(404);
            return $this->response;
        }
        $path = $this->request->get('resource');
        try {
            $params = $this->parse_path($path);
        } catch (\InvalidArgumentException $e) {
            if ($app_mode == \Magento\Framework\App\State::MODE_PRODUCTION) {
                $this->response->set_http_response_code(404);
                return $this->response;
            }
            throw $e;
        }
        if (!($this->is_theme_allowed($params['area'] . DIRECTORY_SEPARATOR . $params['theme']) && $this->locale_validator->is_valid($params['locale']))) {
            if ($app_mode == \Magento\Framework\App\State::MODE_PRODUCTION) {
                $this->response->set_http_response_code(404);
                return $this->response;
            }
            throw new \InvalidArgumentException('Requested path ' . $path . ' is wrong.');
        }
        $this->state->set_area_code($params['area']);
        $this->object_manager->configure($this->config_loader->load($params['area']));
        $file = $params['file'];
        unset($params['file']);
        $asset = $this->asset_repo->create_asset($file, $params);
        $this->response->set_file_path($asset->get_source_file());
        $this->publisher->publish($asset);
        return $this->response;
    }
    /**
     * @inheritdoc
     */
    public function catch_exception(Bootstrap $bootstrap, \Exception $exception)
    {
        $this->get_logger()->critical($exception->get_message());
        if ($bootstrap->is_developer_mode()) {
            $this->response->set_http_response_code(404);
            $this->response->set_header('Content-Type', 'text/plain');
            $this->response->set_body($exception->get_message() . "\n" . Debug::trace($exception->get_trace(), true, true, (bool) getenv('MAGE_DEBUG_SHOW_ARGS')));
            $this->response->send_response();
        } else {
            require $this->get_filesystem()->get_directory_read(Directory_List::PUB)->get_absolute_path('errors/404.php');
        }
        return true;
    }
    /**
     * Parse path to identify parts needed for searching original file
     *
     * @param string $path
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function parse_path($path)
    {
        $path = $path !== null ? ltrim($path, '/') : '';
        $safe_path = $this->driver->get_real_path_safety($path);
        $parts = explode('/', $safe_path, 6);
        if (count($parts) < 5) {
            //Checking that path contains all required parts and is not above static folder.
            throw new \InvalidArgumentException("Requested path '{$path}' is wrong.");
        }
        $result = [];
        $result['area'] = $parts[0];
        $result['theme'] = $parts[1] . '/' . $parts[2];
        $result['locale'] = $parts[3];
        if (count($parts) >= 6 && $this->module_list->has($parts[4])) {
            $result['module'] = $parts[4];
        } else {
            $result['module'] = '';
            if (isset($parts[5])) {
                $parts[5] = $parts[4] . '/' . $parts[5];
            } else {
                $parts[5] = $parts[4];
            }
        }
        $result['file'] = $parts[5];
        return $result;
    }
    /**
     * Lazyload filesystem driver
     *
     * @deprecated 100.1.0
     * @return Filesystem
     */
    private function get_filesystem()
    {
        if (!$this->filesystem) {
            $this->filesystem = $this->object_manager->get(Filesystem::class);
        }
        return $this->filesystem;
    }
    /**
     * Retrieves LoggerInterface instance
     *
     * @return LoggerInterface
     * @deprecated 101.0.0
     */
    private function get_logger()
    {
        if (!$this->logger) {
            $this->logger = $this->object_manager->get(Logger_Interface::class);
        }
        return $this->logger;
    }
    /**
     * Method to check if theme allowed.
     *
     * @param string $theme
     * @return bool
     */
    private function is_theme_allowed(string $theme): bool
    {
        return in_array($theme, array_keys($this->theme_package_list->get_themes()));
    }
}