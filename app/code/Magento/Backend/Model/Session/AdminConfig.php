<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Session;

use Magento\Backend\App\Area\Front_Name_Resolver;
use Magento\Framework\App\Deployment_Config;
use Magento\Framework\Filesystem;
use Magento\Framework\Session\Config;
/**
 * Magento Backend session configuration
 * @api
 * @since 100.0.2
 */
class Admin_Config extends Config
{
    /**
     * Configuration for admin session name
     */
    public const SESSION_NAME_ADMIN = 'admin';
    /**
     * @var FrontNameResolver
     */
    protected $_front_name_resolver;
    /**
     * @var \Magento\Backend\App\BackendAppList
     */
    private $backend_app_list;
    /**
     * @var \Magento\Backend\Model\UrlFactory
     */
    private $backend_url_factory;
    /**
     * @param \Magento\Framework\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Filesystem $filesystem
     * @param DeploymentConfig $deploymentConfig
     * @param string $scopeType
     * @param \Magento\Backend\App\BackendAppList $backendAppList
     * @param FrontNameResolver $frontNameResolver
     * @param \Magento\Backend\Model\UrlFactory $backendUrlFactory
     * @param string $lifetimePath
     * @param string $sessionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\Validator_Factory $validator_factory, \Magento\Framework\App\Config\Scope_Config_Interface $scope_config, \Magento\Framework\Stdlib\String_Utils $string_helper, \Magento\Framework\App\Request_Interface $request, Filesystem $filesystem, Deployment_Config $deployment_config, $scope_type, \Magento\Backend\App\Backend_App_List $backend_app_list, Front_Name_Resolver $front_name_resolver, \Magento\Backend\Model\Url_Factory $backend_url_factory, $lifetime_path = self::XML_PATH_COOKIE_LIFETIME, $session_name = self::SESSION_NAME_ADMIN)
    {
        parent::__construct($validator_factory, $scope_config, $string_helper, $request, $filesystem, $deployment_config, $scope_type, $lifetime_path);
        $this->_front_name_resolver = $front_name_resolver;
        $this->backend_app_list = $backend_app_list;
        $this->backend_url_factory = $backend_url_factory;
        $admin_path = $this->extract_admin_path();
        $this->set_cookie_path($admin_path);
        $this->set_name($session_name);
        $this->set_cookie_secure($this->_http_request->is_secure());
        $this->set_cookie_same_site('Lax');
    }
    /**
     * Determine the admin path
     *
     * @return string
     */
    private function extract_admin_path()
    {
        $backend_app = $this->backend_app_list->get_current_app();
        $cookie_path = null;
        //phpcs:ignore
        $base_url = parse_url($this->backend_url_factory->create()->get_base_url(), PHP_URL_PATH);
        if (!$backend_app) {
            $cookie_path = $base_url . $this->_front_name_resolver->get_front_name();
            return $cookie_path;
        }
        //In case of application authenticating through the admin login, the script name should be removed
        //from the path, because application has own script.
        $base_url = \Magento\Framework\App\Request\Http::get_url_no_script($base_url);
        $cookie_path = $base_url . $backend_app->get_cookie_path();
        return $cookie_path;
    }
    /**
     * Set session cookie lifetime to session duration
     *
     * @return $this
     * @since 100.1.0
     */
    protected function configure_cookie_lifetime()
    {
        return $this->set_cookie_lifetime(0);
    }
}