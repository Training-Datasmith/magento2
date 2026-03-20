<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\Host_Checker;
/**
 * Class \Magento\Backend\Model\UrlInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @api
 * @since 100.0.2
 */
class Url extends \Magento\Framework\Url implements \Magento\Backend\Model\Url_Interface
{
    /**
     * Whether to use a security key in the backend
     *
     * @bug Currently, this constant is slightly misleading: it says "form key", but in fact it is used by URLs, too
     */
    public const XML_PATH_USE_SECURE_KEY = 'admin/security/use_form_key';
    /**
     * Authentication session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;
    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $_menu;
    /**
     * Startup page url from config
     *
     * @var string
     */
    protected $_startup_menu_item_id;
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backend_helper;
    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    protected $_menu_config;
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_store_factory;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $form_key;
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_scope;
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo
     * @param \Magento\Framework\Url\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Url\RouteParamsResolverFactory $routeParamsResolverFactory
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Url\RouteParamsPreprocessorInterface $routeParamsPreprocessor
     * @param string $scopeType
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param Menu\Config $menuConfig
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param Auth\Session $authSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     * @param HostChecker|null $hostChecker
     * @param Json $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\App\Route\Config_Interface $route_config, \Magento\Framework\App\Request_Interface $request, \Magento\Framework\Url\Security_Info_Interface $url_security_info, \Magento\Framework\Url\Scope_Resolver_Interface $scope_resolver, \Magento\Framework\Session\Generic $session, \Magento\Framework\Session\Sid_Resolver_Interface $sid_resolver, \Magento\Framework\Url\Route_Params_Resolver_Factory $route_params_resolver_factory, \Magento\Framework\Url\Query_Params_Resolver_Interface $query_params_resolver, \Magento\Framework\App\Config\Scope_Config_Interface $scope_config, \Magento\Framework\Url\Route_Params_Preprocessor_Interface $route_params_preprocessor, $scope_type, \Magento\Backend\Helper\Data $backend_helper, \Magento\Backend\Model\Menu\Config $menu_config, \Magento\Framework\App\Cache_Interface $cache, \Magento\Backend\Model\Auth\Session $auth_session, \Magento\Framework\Encryption\Encryptor_Interface $encryptor, \Magento\Store\Model\Store_Factory $store_factory, \Magento\Framework\Data\Form\Form_Key $form_key, array $data = [], ?Host_Checker $host_checker = null, ?Json $serializer = null)
    {
        $this->_encryptor = $encryptor;
        $host_checker = $host_checker ?: Object_Manager::get_instance()->get(Host_Checker::class);
        parent::__construct($route_config, $request, $url_security_info, $scope_resolver, $session, $sid_resolver, $route_params_resolver_factory, $query_params_resolver, $scope_config, $route_params_preprocessor, $scope_type, $data, $host_checker, $serializer);
        $this->_backend_helper = $backend_helper;
        $this->_menu_config = $menu_config;
        $this->_cache = $cache;
        $this->_session = $auth_session;
        $this->form_key = $form_key;
        $this->_store_factory = $store_factory;
    }
    /**
     * Retrieve is secure mode for ULR logic
     *
     * @return bool
     */
    protected function _is_secure()
    {
        if ($this->has_data('secure_is_forced')) {
            return $this->get_data('secure');
        }
        return $this->_scope_config->is_set_flag('web/secure/use_in_adminhtml');
    }
    /**
     * Force strip secret key param if _nosecret param specified
     *
     * @param array $data
     * @param bool $unsetOldParams
     * @return $this
     */
    protected function _set_route_params(array $data, $unset_old_params = true)
    {
        if (isset($data['_nosecret'])) {
            $this->set_no_secret(true);
            unset($data['_nosecret']);
        } else {
            $this->set_no_secret(false);
        }
        unset($data['_scope_to_url']);
        return parent::_set_route_params($data, $unset_old_params);
    }
    /**
     * Custom logic to retrieve Urls
     *
     * @param string $routePath
     * @param array $routeParams
     * @return string
     */
    public function get_url($route_path = null, $route_params = null)
    {
        if (filter_var($route_path, FILTER_VALIDATE_URL)) {
            return $route_path;
        }
        $cache_secret_key = false;
        if (isset($route_params['_cache_secret_key'])) {
            unset($route_params['_cache_secret_key']);
            $cache_secret_key = true;
        }
        $result = parent::get_url($route_path, $route_params);
        if (!$this->use_secret_key()) {
            return $result;
        }
        $this->get_route_params_resolver()->unset_data('route_params');
        $this->_set_route_path($route_path);
        $extra_params = $this->get_route_params_resolver()->get_route_params();
        $route_name = $this->_get_route_name('*');
        $controller_name = $this->_get_controller_name(self::DEFAULT_CONTROLLER_NAME);
        $action_name = $this->_get_action_name(self::DEFAULT_ACTION_NAME);
        if (!isset($route_params[self::SECRET_KEY_PARAM_NAME])) {
            if (!is_array($route_params)) {
                $route_params = [];
            }
            $secret_key = $cache_secret_key ? "\${$route_name}/{$controller_name}/{$action_name}\$" : $this->get_secret_key($route_name, $controller_name, $action_name);
            $route_params[self::SECRET_KEY_PARAM_NAME] = $secret_key;
        }
        if (!empty($extra_params)) {
            $route_params = array_merge($extra_params, $route_params);
        }
        return parent::get_url("{$route_name}/{$controller_name}/{$action_name}", $route_params);
    }
    /**
     * Generate secret key for controller and action based on form key
     *
     * @param string $routeName
     * @param string $controller Controller name
     * @param string $action Action name
     * @return string
     */
    public function get_secret_key($route_name = null, $controller = null, $action = null)
    {
        $salt = $this->form_key->get_form_key();
        $request = $this->_get_request();
        if (!$route_name) {
            if ($request->get_before_forward_info('route_name') !== null) {
                $route_name = $request->get_before_forward_info('route_name');
            } else {
                $route_name = $request->get_route_name();
            }
        }
        if (!$controller) {
            if ($request->get_before_forward_info('controller_name') !== null) {
                $controller = $request->get_before_forward_info('controller_name');
            } else {
                $controller = $request->get_controller_name();
            }
        }
        if (!$action) {
            if ($request->get_before_forward_info('action_name') !== null) {
                $action = $request->get_before_forward_info('action_name');
            } else {
                $action = $request->get_action_name();
            }
        }
        $secret = $route_name . $controller . $action . $salt;
        return $this->_encryptor->get_hash($secret);
    }
    /**
     * Return secret key settings flag
     *
     * @return bool
     */
    public function use_secret_key()
    {
        return $this->_scope_config->is_set_flag(self::XML_PATH_USE_SECURE_KEY) && !$this->get_no_secret();
    }
    /**
     * Enable secret key using
     *
     * @return $this
     */
    public function turn_on_secret_key()
    {
        $this->set_no_secret(false);
        return $this;
    }
    /**
     * Disable secret key using
     *
     * @return $this
     */
    public function turn_off_secret_key()
    {
        $this->set_no_secret(true);
        return $this;
    }
    /**
     * Refresh admin menu cache etc.
     *
     * @return void
     */
    public function renew_secret_urls()
    {
        $this->_cache->clean([\Magento\Backend\Block\Menu::CACHE_TAGS]);
    }
    /**
     * Find admin start page url
     *
     * @return string
     */
    public function get_startup_page_url()
    {
        $menu_item = $this->_get_menu()->get($this->_scope_config->get_value(self::XML_PATH_STARTUP_MENU_ITEM, $this->_scope_type));
        if ($menu_item !== null) {
            if ($menu_item->is_allowed() && $menu_item->get_action()) {
                return $menu_item->get_action();
            }
        }
        return $this->find_first_available_menu();
    }
    /**
     * Find first menu item that user is able to access
     *
     * @return string
     */
    public function find_first_available_menu()
    {
        /* @var $menu \Magento\Backend\Model\Menu\Item */
        $menu = $this->_get_menu();
        $item = $menu->get_first_available();
        $action = $item ? $item->get_action() : null;
        if (!$item) {
            $user = $this->_get_session()->get_user();
            if ($user) {
                $user->set_has_available_resources(false);
            }
            $action = '*/denied';
        }
        return $action;
    }
    /**
     * Get Menu model
     *
     * @return \Magento\Backend\Model\Menu
     */
    protected function _get_menu()
    {
        if ($this->_menu === null) {
            $this->_menu = $this->_menu_config->get_menu();
        }
        return $this->_menu;
    }
    /**
     * Set scope entity
     *
     * @param mixed $scopeId
     * @return \Magento\Framework\UrlInterface
     * @since 101.0.3
     */
    public function set_scope($scope_id)
    {
        parent::set_scope($scope_id);
        $this->_scope = $this->_scope_resolver->get_scope($scope_id);
        return $this;
    }
    /**
     * Set custom auth session
     *
     * @param \Magento\Backend\Model\Auth\Session $session
     * @return $this
     */
    public function set_session(\Magento\Backend\Model\Auth\Session $session)
    {
        $this->_session = $session;
        return $this;
    }
    /**
     * Retrieve auth session
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    protected function _get_session()
    {
        return $this->_session;
    }
    /**
     * Return backend area front name, defined in configuration
     *
     * @return string
     */
    public function get_area_front_name()
    {
        if (!$this->_get_data('area_front_name')) {
            $this->set_data('area_front_name', $this->_backend_helper->get_area_front_name());
        }
        return $this->_get_data('area_front_name');
    }
    /**
     * Retrieve action path, add backend area front name as a prefix to action path
     *
     * @return string
     */
    protected function _get_action_path()
    {
        $path = parent::_get_action_path();
        if ($path) {
            if ($this->get_area_front_name()) {
                $path = $this->get_area_front_name() . '/' . $path;
            }
        }
        return $path;
    }
    /**
     * Get scope for the url instance
     *
     * @return \Magento\Store\Model\Store
     */
    protected function _get_scope()
    {
        if (!$this->_scope) {
            $this->_scope = $this->_store_factory->create(['url' => $this, 'data' => ['code' => 'admin', 'force_disable_rewrites' => false, 'disable_store_in_url' => true]]);
        }
        return $this->_scope;
    }
    /**
     * Get cache id for config path
     *
     * @param string $path
     * @return string
     */
    protected function _get_config_cache_id($path)
    {
        return 'admin/' . $path;
    }
    /**
     * Get config data by path, use only global config values for backend
     *
     * @param string $path
     * @return null|string
     */
    protected function _get_config($path)
    {
        return $this->_scope_config->get_value($path);
    }
}