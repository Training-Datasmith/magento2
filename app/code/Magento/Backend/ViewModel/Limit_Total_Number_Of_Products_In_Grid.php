<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\View_Model;

use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\View\Element\Block\Argument_Interface;
/**
 * Get configuration values related to limit total number of products in grid collection.
 */
class Limit_Total_Number_Of_Products_In_Grid implements Argument_Interface
{
    /**
     * @var ScopeConfigInterface
     */
    private Scope_Config_Interface $scope_config;
    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Scope_Config_Interface $scope_config)
    {
        $this->scope_config = $scope_config;
    }
    /**
     * Check if configuration setting to limit total number of products in grid is enabled.
     *
     * @return bool
     */
    public function limit_total_number_of_products(): bool
    {
        return (bool) $this->scope_config->get_value('admin/grid/limit_total_number_of_products');
    }
    /**
     * Get records threshold for limit total number of products in collection.
     *
     * @return int
     */
    public function get_records_limit(): int
    {
        return (int) $this->scope_config->get_value('admin/grid/records_limit');
    }
}