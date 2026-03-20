<?php

/**
 * Base router
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Router;

/**
 * Base router implementation.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Base implements \Magento\Framework\App\Router_Interface
{
    /**
     * No route constant used for request
     */
    public const NO_ROUTE = 'noroute';
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $action_factory;
    /**
     * @var string
     */
    protected $action_interface = \Magento\Framework\App\Action_Interface::class;
    /**
     * @var array
     */
    protected $_modules = [];
    /**
     * @var array
     */
    protected $_dispatch_data = [];
    /**
     * List of required request parameters
     * Order sensitive
     * @var string[]
     */
    protected $_required_params = ['moduleFrontName', 'actionPath', 'actionName'];
    /**
     * @var \Magento\Framework\App\Route\ConfigInterface
     */
    protected $_route_config;
    /**
     * Url security information.
     *
     * @var \Magento\Framework\Url\SecurityInfoInterface
     */
    protected $_url_security_info;
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scope_config;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_store_manager;
    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_response_factory;
    /**
     * @var \Magento\Framework\App\DefaultPathInterface
     */
    protected $_default_path;
    /**
     * @var \Magento\Framework\Code\NameBuilder
     */
    protected $name_builder;
    /**
     * @var array
     */
    protected $reserved_names = ['new', 'print', 'switch', 'return'];
    /**
     * Allows to control if we need to enable no route functionality in current router
     *
     * @var bool
     */
    protected $apply_no_route = false;
    /**
     * @var string
     */
    protected $path_prefix = null;
    /**
     * @var \Magento\Framework\App\Router\ActionList
     */
    protected $action_list;
    /**
     * @var \Magento\Framework\App\Router\PathConfigInterface
     */
    protected $path_config;
    /**
     * @param \Magento\Framework\App\Router\ActionList $actionList
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Code\NameBuilder $nameBuilder
     * @param \Magento\Framework\App\Router\PathConfigInterface $pathConfig
     *
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(\Magento\Framework\App\Router\Action_List $action_list, \Magento\Framework\App\Action_Factory $action_factory, \Magento\Framework\App\Default_Path_Interface $default_path, \Magento\Framework\App\Response_Factory $response_factory, \Magento\Framework\App\Route\Config_Interface $route_config, \Magento\Framework\Url_Interface $url, \Magento\Framework\Code\Name_Builder $name_builder, \Magento\Framework\App\Router\Path_Config_Interface $path_config)
    {
        $this->action_list = $action_list;
        $this->action_factory = $action_factory;
        $this->_response_factory = $response_factory;
        $this->_default_path = $default_path;
        $this->_route_config = $route_config;
        $this->_url = $url;
        $this->name_builder = $name_builder;
        $this->path_config = $path_config;
    }
    /**
     * Match provided request and if matched - return corresponding controller
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(\Magento\Framework\App\Request_Interface $request)
    {
        $params = $this->parse_request($request);
        return $this->match_action($request, $params);
    }
    /**
     * Parse request URL params
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return array
     */
    protected function parse_request(\Magento\Framework\App\Request_Interface $request)
    {
        $output = [];
        $path = trim($request->get_path_info(), '/');
        $params = explode('/', strlen($path) ? $path : $this->path_config->get_default_path());
        foreach ($this->_required_params as $param_name) {
            $output[$param_name] = array_shift($params);
        }
        for ($i = 0, $l = count($params); $i < $l; $i += 2) {
            $output['variables'][$params[$i]] = isset($params[$i + 1]) ? urldecode($params[$i + 1]) : '';
        }
        return $output;
    }
    /**
     * Match module front name
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $param
     * @return string|null
     */
    protected function match_module_front_name(\Magento\Framework\App\Request_Interface $request, $param)
    {
        // get module name
        if ($request->get_module_name()) {
            $module_front_name = $request->get_module_name();
        } elseif (strlen((string) $param)) {
            $module_front_name = $param;
        } else {
            $module_front_name = $this->_default_path->get_part('module');
            $request->set_alias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, '');
            if (!$module_front_name) {
                return null;
            }
        }
        return $module_front_name;
    }
    /**
     * Match controller name
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $param
     * @return string
     */
    protected function match_action_path(\Magento\Framework\App\Request_Interface $request, $param)
    {
        if ($request->get_controller_name()) {
            $action_path = $request->get_controller_name();
        } elseif (!empty($param)) {
            $action_path = $param;
        } else {
            $action_path = $this->_default_path->get_part('controller');
            $request->set_alias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, ltrim($request->get_original_path_info(), '/'));
        }
        return $action_path;
    }
    /**
     * Get not found controller instance
     *
     * @param string $currentModuleName
     * @return \Magento\Framework\App\ActionInterface|null
     */
    protected function get_not_found_action($current_module_name)
    {
        if (!$this->apply_no_route) {
            return null;
        }
        $action_class_name = $this->get_action_class_name($current_module_name, 'noroute');
        if (!$action_class_name || !is_subclass_of($action_class_name, $this->action_interface)) {
            return null;
        }
        // instantiate action class
        return $this->action_factory->create($action_class_name);
    }
    /**
     * Create matched controller instance
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $params
     * @return \Magento\Framework\App\ActionInterface|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function match_action(\Magento\Framework\App\Request_Interface $request, array $params)
    {
        $module_front_name = $this->match_module_front_name($request, $params['moduleFrontName']);
        if (!strlen((string) $module_front_name)) {
            return null;
        }
        /**
         * Searching router args by module name from route using it as key
         */
        $modules = $this->_route_config->get_modules_by_front_name($module_front_name);
        if (empty($modules) === true) {
            return null;
        }
        /**
         * Going through modules to find appropriate controller
         */
        $current_module_name = null;
        $action_path = null;
        $action = null;
        $action_instance = null;
        $action_path = $this->match_action_path($request, $params['actionPath']);
        $action = $request->get_action_name() ?: ($params['actionName'] ?: $this->_default_path->get_part('action'));
        $this->_check_should_be_secure($request, '/' . $module_front_name . '/' . $action_path . '/' . $action);
        foreach ($modules as $module_name) {
            $current_module_name = $module_name;
            $action_class_name = $this->action_list->get($module_name, $this->path_prefix, $action_path, $action);
            if (!$action_class_name || !is_subclass_of($action_class_name, $this->action_interface)) {
                continue;
            }
            $action_instance = $this->action_factory->create($action_class_name);
            break;
        }
        if (null == $action_instance) {
            $action_instance = $this->get_not_found_action($current_module_name);
            if ($action_instance === null) {
                return null;
            }
            $action = self::NO_ROUTE;
        }
        // set values only after all the checks are done
        $request->set_module_name($module_front_name);
        $request->set_controller_name($action_path);
        $request->set_action_name($action);
        $request->set_controller_module($current_module_name);
        $request->set_route_name($this->_route_config->get_route_by_front_name($module_front_name));
        if (isset($params['variables'])) {
            $request->set_params($params['variables']);
        }
        return $action_instance;
    }
    /**
     * Build controller class name
     *
     * @param string $module
     * @param string $actionPath
     * @return string
     */
    public function get_action_class_name($module, $action_path)
    {
        $prefix = $this->path_prefix ? 'Controller\\' . $this->path_prefix : 'Controller';
        return $this->name_builder->build_class_name([$module, $prefix, $action_path]);
    }
    /**
     * Check that request uses https protocol if it should.
     *
     * Function redirects user to correct URL if needed.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $path
     * @return void
     */
    protected function _check_should_be_secure(\Magento\Framework\App\Request_Interface $request, $path = '')
    {
        if ($request->get_post_value()) {
            return;
        }
        if ($this->path_config->should_be_secure($path) && !$request->is_secure()) {
            $url = $this->path_config->get_current_secure_url($request);
            if ($this->_should_redirect_to_secure()) {
                $url = $this->_url->get_redirect_url($url);
            }
            $this->_response_factory->create()->set_redirect($url)->send_response();
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            exit;
        }
    }
    /**
     * Check whether redirect url should be used for secure routes
     *
     * @return bool
     */
    protected function _should_redirect_to_secure()
    {
        return false;
    }
}