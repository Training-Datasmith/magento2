<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model;

use Magento\Framework\App\Router\Path_Config_Interface;
use Magento\Store\Model\Store;
/**
 * Path config to be used in adminhtml area
 * @api
 * @since 100.0.2
 */
class Admin_Path_Config implements Path_Config_Interface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $core_config;
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backend_config;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(\Magento\Framework\App\Config\Scope_Config_Interface $core_config, \Magento\Backend\App\Config_Interface $backend_config, \Magento\Framework\Url_Interface $url)
    {
        $this->core_config = $core_config;
        $this->backend_config = $backend_config;
        $this->url = $url;
    }
    /**
     * @inheritdoc
     */
    public function get_current_secure_url(\Magento\Framework\App\Request_Interface $request)
    {
        return $this->url->get_base_url('link', true) . ltrim($request->get_path_info(), '/');
    }
    /**
     * @inheritdoc
     */
    public function should_be_secure($path)
    {
        $base_url = (string) $this->core_config->get_value(Store::XML_PATH_UNSECURE_BASE_URL, 'default');
        if (parse_url($base_url, PHP_URL_SCHEME) === 'https') {
            return true;
        }
        if ($this->backend_config->is_set_flag(Store::XML_PATH_SECURE_IN_ADMINHTML)) {
            if ($this->backend_config->is_set_flag('admin/url/use_custom')) {
                $admin_base_url = (string) $this->core_config->get_value('admin/url/custom', 'default');
            } else {
                $admin_base_url = (string) $this->core_config->get_value(Store::XML_PATH_SECURE_BASE_URL, 'default');
            }
            return parse_url($admin_base_url, PHP_URL_SCHEME) === 'https';
        }
        return false;
    }
    /**
     * @inheritdoc
     */
    public function get_default_path()
    {
        return $this->backend_config->get_value('web/default/admin');
    }
}