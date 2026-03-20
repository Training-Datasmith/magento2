<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\View_Model;

use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\Url_Interface;
use Magento\Framework\View\Element\Block\Argument_Interface;
use Magento\Store\Model\Scope_Interface;
/**
 * View model for dashboard chart disabled notice
 */
class Chart_Disabled implements Argument_Interface
{
    /**
     * Location of the "Enable Chart" config param
     */
    private const XML_PATH_ENABLE_CHARTS = 'admin/dashboard/enable_charts';
    /**
     * Route to Stores -> Configuration section
     */
    private const ROUTE_SYSTEM_CONFIG = 'adminhtml/system_config/edit';
    /**
     * @var UrlInterface
     */
    private $url_builder;
    /**
     * @var ScopeConfigInterface
     */
    private $scope_config;
    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Url_Interface $url_builder, Scope_Config_Interface $scope_config)
    {
        $this->url_builder = $url_builder;
        $this->scope_config = $scope_config;
    }
    /**
     * Get url to dashboard chart configuration
     *
     * @return string
     */
    public function get_config_url(): string
    {
        return $this->url_builder->get_url(self::ROUTE_SYSTEM_CONFIG, ['section' => 'admin', '_fragment' => 'admin_dashboard-link']);
    }
    /**
     * Check if dashboard chart is enabled
     *
     * @return bool
     */
    public function is_chart_enabled(): bool
    {
        return $this->scope_config->is_set_flag(self::XML_PATH_ENABLE_CHARTS, Scope_Interface::SCOPE_STORE);
    }
}