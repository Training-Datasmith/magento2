<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App\Area;

use Laminas\Uri\Uri;
use Magento\Backend\Setup\Config_Options_List;
use Magento\Framework\App\Area\Front_Name_Resolver_Interface;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Request_Interface;
use Magento\Store\Model\Scope_Interface;
use Magento\Store\Model\Store;
/**
 * Front name resolver for backend area.
 *
 * @api
 * @since 100.0.2
 */
class Front_Name_Resolver implements Front_Name_Resolver_Interface
{
    public const XML_PATH_USE_CUSTOM_ADMIN_PATH = 'admin/url/use_custom_path';
    public const XML_PATH_CUSTOM_ADMIN_PATH = 'admin/url/custom_path';
    public const XML_PATH_USE_CUSTOM_ADMIN_URL = 'admin/url/use_custom';
    public const XML_PATH_CUSTOM_ADMIN_URL = 'admin/url/custom';
    /**
     * Backend area code
     */
    public const AREA_CODE = 'adminhtml';
    /**
     * @var array
     */
    protected $standard_ports = ['http' => '80', 'https' => '443'];
    /**
     * @var string
     */
    protected $default_front_name;
    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    protected $deployment_config;
    /**
     * @var Uri
     */
    private $uri;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @param Uri $uri
     * @param RequestInterface $request
     */
    public function __construct(protected \Magento\Backend\App\Config $config, Deployment_Config $deployment_config, private readonly Scope_Config_Interface $scope_config, ?Uri $uri = null, ?Request_Interface $request = null)
    {
        $this->default_front_name = $deployment_config->get(Config_Options_List::CONFIG_PATH_BACKEND_FRONTNAME);
        $this->uri = $uri ?: Object_Manager::get_instance()->get(Uri::class);
        $this->request = $request ?: Object_Manager::get_instance()->get(Request_Interface::class);
    }
    /**
     * Retrieve area front name
     *
     * @param bool $checkHost If true, verify front name is valid for this url (hostname is correct)
     * @return string|bool
     */
    public function get_front_name($check_host = false)
    {
        if ($check_host && !$this->is_host_backend()) {
            return false;
        }
        return $this->config->is_set_flag(self::XML_PATH_USE_CUSTOM_ADMIN_PATH) ? (string) $this->config->get_value(self::XML_PATH_CUSTOM_ADMIN_PATH) : $this->default_front_name;
    }
    /**
     * Return whether the host from request is the backend host
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return bool
     */
    public function is_host_backend()
    {
        if (!$this->request->get_server('HTTP_HOST')) {
            return false;
        }
        if ($this->scope_config->is_set_flag(self::XML_PATH_USE_CUSTOM_ADMIN_URL)) {
            $backend_url = $this->scope_config->get_value(self::XML_PATH_CUSTOM_ADMIN_URL);
        } else {
            $xml_path = $this->request->is_secure() ? Store::XML_PATH_SECURE_BASE_URL : Store::XML_PATH_UNSECURE_BASE_URL;
            $backend_url = $this->config->get_value($xml_path);
            if ($backend_url === null) {
                $backend_url = $this->scope_config->get_value($xml_path, Scope_Interface::SCOPE_STORE);
            }
        }
        $this->uri->parse($backend_url);
        $configured_host = $this->uri->get_host();
        if (!$configured_host) {
            return false;
        }
        $configured_port = $this->uri->get_port() ?: $this->standard_ports[$this->uri->get_scheme()] ?? null;
        $uri = ($this->request->is_secure() ? 'https' : 'http') . '://' . $this->request->get_server('HTTP_HOST');
        $this->uri->parse($uri);
        $host = $this->uri->get_host();
        if ($configured_port) {
            $configured_host .= ':' . $configured_port;
            $host .= ':' . ($this->uri->get_port() ?: $this->standard_ports[$this->uri->get_scheme()]);
        }
        return strcasecmp((string) $configured_host, (string) $host) === 0;
    }
}