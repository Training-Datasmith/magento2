<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request;

use Laminas\Stdlib\Parameters;
use Magento\Framework\App\Http_Request_Interface;
use Magento\Framework\App\Request_Content_Interface;
use Magento\Framework\App\Request_Safety_Interface;
use Magento\Framework\App\Route\Config_Interface;
use Magento\Framework\HTTP\Php_Environment\Request;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Stdlib\Cookie\Cookie_Reader_Interface;
use Magento\Framework\Stdlib\String_Utils;
/**
 * Http request
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @api
 */
class Http extends Request implements Request_Content_Interface, Request_Safety_Interface, Http_Request_Interface, Reset_After_Request_Interface
{
    /**#@+
     * HTTP Ports
     */
    public const DEFAULT_HTTP_PORT = 80;
    public const DEFAULT_HTTPS_PORT = 443;
    /**#@-*/
    // Configuration path
    public const XML_PATH_OFFLOADER_HEADER = 'web/secure/offloader_header';
    /**
     * @var string
     */
    protected $route;
    /**
     * @var string
     */
    protected $path_info = '';
    /**
     * @var string
     */
    protected $original_path_info = '';
    /**
     * @var array
     */
    protected $direct_front_names;
    /**
     * @var string
     */
    protected $controller_module;
    /**
     * Request's original information before forward.
     *
     * @var array
     */
    protected $before_forward_info = [];
    /**
     * @var ConfigInterface
     */
    protected $route_config;
    /**
     * @var PathInfoProcessorInterface
     */
    protected $path_info_processor;
    /**
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @var bool|null
     */
    protected $is_safe_method = null;
    /**
     * @var array
     */
    protected $safe_request_types = ['GET', 'HEAD', 'TRACE', 'OPTIONS'];
    /**
     * @var string
     */
    private $distro_base_url;
    /**
     * @var PathInfo
     */
    private $path_info_service;
    /**
     * @param CookieReaderInterface $cookieReader
     * @param StringUtils $converter
     * @param ConfigInterface $routeConfig
     * @param PathInfoProcessorInterface $pathInfoProcessor
     * @param ObjectManagerInterface $objectManager
     * @param \Laminas\Uri\UriInterface|string|null $uri
     * @param array $directFrontNames
     * @param PathInfo|null $pathInfoService
     */
    public function __construct(Cookie_Reader_Interface $cookie_reader, String_Utils $converter, Config_Interface $route_config, Path_Info_Processor_Interface $path_info_processor, Object_Manager_Interface $object_manager, $uri = null, $direct_front_names = [], ?Path_Info $path_info_service = null)
    {
        parent::__construct($cookie_reader, $converter, $uri);
        $this->route_config = $route_config;
        $this->path_info_processor = $path_info_processor;
        $this->object_manager = $object_manager;
        $this->direct_front_names = $direct_front_names;
        $this->path_info_service = $path_info_service ?: \Magento\Framework\App\Object_Manager::get_instance()->get(Path_Info::class);
    }
    /**
     * Return the ORIGINAL_PATH_INFO.
     * This value is calculated and processed from $_SERVER due to cross-platform differences.
     * instead of reading PATH_INFO
     *
     * @return string
     */
    public function get_original_path_info()
    {
        if (empty($this->original_path_info)) {
            $original_path_info_from_request = $this->path_info_service->get_path_info($this->get_request_uri(), $this->get_base_url());
            $this->original_path_info = (string) $this->path_info_processor->process($this, $original_path_info_from_request);
            $this->request_string = $this->original_path_info . $this->path_info_service->get_query_string($this->get_request_uri());
        }
        return $this->original_path_info;
    }
    /**
     * Return the path info
     *
     * @return string
     */
    public function get_path_info()
    {
        if (empty($this->path_info)) {
            $this->path_info = $this->get_original_path_info();
        }
        return $this->path_info;
    }
    /**
     * Set the PATH_INFO string.
     *
     * Set the ORIGINAL_PATH_INFO string.
     *
     * @param string|null $pathInfo
     * @return $this
     */
    public function set_path_info($path_info = null)
    {
        $this->path_info = (string) $path_info;
        return $this;
    }
    /**
     * Check if code declared as direct access frontend name.
     *
     * This means what this url can be used without store code.
     *
     * @param   string $code
     * @return  bool
     */
    public function is_direct_access_frontend_name($code)
    {
        return isset($this->direct_front_names[$code]);
    }
    /**
     * Get base path
     *
     * @return string
     */
    public function get_base_path()
    {
        $path = parent::get_base_path();
        return empty($path) ? '/' : str_replace('\\', '/', $path);
    }
    /**
     * Retrieve request front name
     *
     * @return string|null
     */
    public function get_front_name()
    {
        $path_parts = explode('/', trim($this->get_path_info(), '/'));
        return reset($path_parts);
    }
    /**
     * Set route name
     *
     * @param string $route
     * @return $this
     */
    public function set_route_name($route)
    {
        $this->route = $route;
        $module = $this->route_config->get_route_front_name($route);
        if ($module) {
            $this->set_module_name($module);
        }
        return $this;
    }
    /**
     * Retrieve route name
     *
     * @return string|null
     */
    public function get_route_name()
    {
        return $this->route;
    }
    /**
     * Specify module name where was found currently used controller
     *
     * @param string $module
     * @return $this
     */
    public function set_controller_module($module)
    {
        $this->controller_module = $module;
        return $this;
    }
    /**
     * Get module name of currently used controller
     *
     * @return string
     */
    public function get_controller_module()
    {
        return $this->controller_module;
    }
    /**
     * Collect properties changed by _forward in protected storage before _forward was called first time.
     *
     * @return $this
     */
    public function init_forward()
    {
        if (empty($this->before_forward_info)) {
            $this->before_forward_info = ['params' => $this->get_params(), 'action_name' => $this->get_action_name(), 'controller_name' => $this->get_controller_name(), 'module_name' => $this->get_module_name(), 'route_name' => $this->get_route_name()];
        }
        return $this;
    }
    /**
     * Retrieve property's value which was before _forward call.
     * If property was not changed during _forward call null will be returned.
     * If passed name will be null whole state array will be returned.
     *
     * @param string $name
     * @return array|string|null
     */
    public function get_before_forward_info($name = null)
    {
        if ($name === null) {
            return $this->before_forward_info;
        }
        return $this->before_forward_info[$name] ?? null;
    }
    /**
     * Check is Request from AJAX
     *
     * @return boolean
     */
    public function is_ajax()
    {
        return $this->is_xml_http_request() || $this->get_param('ajax') || $this->get_param('isAjax');
    }
    /**
     * Get website instance base url
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function get_distro_base_url()
    {
        if ($this->distro_base_url) {
            return $this->distro_base_url;
        }
        $header_http_host = $this->get_server('HTTP_HOST');
        $header_http_host = $this->converter->clean_string($header_http_host);
        $header_script_name = $this->get_server('SCRIPT_NAME');
        if (isset($header_script_name) && $header_http_host !== '') {
            if ($secure = $this->is_secure()) {
                $scheme = 'https://';
            } else {
                $scheme = 'http://';
            }
            $host_arr = explode(':', $header_http_host);
            $host = $host_arr[0];
            $port = isset($host_arr[1]) && (!$secure && $host_arr[1] != 80 || $secure && $host_arr[1] != 443) ? ':' . $host_arr[1] : '';
            $path = $this->get_base_path();
            return $this->distro_base_url = $scheme . $host . $port . rtrim($path, '/') . '/';
        }
        return 'http://localhost/';
    }
    /**
     * Determines a base URL path from environment
     *
     * @param array $server
     * @return string
     */
    public static function get_distro_base_url_path($server)
    {
        $result = '';
        if (isset($server['SCRIPT_NAME'])) {
            $env_path = str_replace('\\', '/', dirname(str_replace('\\', '/', $server['SCRIPT_NAME'])));
            if ($env_path !== '.' && $env_path !== '/') {
                $result = $env_path;
            }
        }
        if (!preg_match('/\/$/', $result)) {
            $result .= '/';
        }
        return $result;
    }
    /**
     * Return url with no script name
     *
     * @param  string $url
     * @return string
     */
    public static function get_url_no_script($url)
    {
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return $url;
        }
        if ($url !== null && ($pos = strripos($url, basename($_SERVER['SCRIPT_NAME']))) !== false) {
            $url = substr($url, 0, $pos);
        }
        return $url;
    }
    /**
     * Retrieve full action name
     *
     * @param string $delimiter
     * @return string
     */
    public function get_full_action_name($delimiter = '_')
    {
        return $this->get_route_name() . $delimiter . $this->get_controller_name() . $delimiter . $this->get_action_name();
    }
    /**
     * Sleep
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    public function is_safe_method()
    {
        if ($this->is_safe_method === null) {
            if (isset($_SERVER['REQUEST_METHOD']) && in_array($_SERVER['REQUEST_METHOD'], $this->safe_request_types)) {
                $this->is_safe_method = true;
            } else {
                $this->is_safe_method = false;
            }
        }
        return $this->is_safe_method;
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->set_env(new Parameters($_ENV));
        $this->server_params = new Parameters($_SERVER);
        $this->set_query(new Parameters([]));
        $this->set_post(new Parameters([]));
        $this->set_files(new Parameters([]));
        $this->module = null;
        $this->controller = null;
        $this->action = null;
        $this->path_info = '';
        $this->request_string = '';
        $this->params = [];
        $this->aliases = [];
        $this->dispatched = false;
        $this->forwarded = null;
        $this->base_url = null;
        $this->base_path = null;
        $this->request_uri = null;
        $this->method = 'GET';
        $this->allow_custom_methods = true;
        $this->uri = null;
        $this->headers = null;
        $this->metadata = [];
        $this->content = '';
        $this->distro_base_url = null;
    }
}