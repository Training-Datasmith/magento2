<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

/**
 * @api
 * @since 100.0.2
 */
interface Scope_Config_Interface
{
    /**
     * Default scope type
     */
    public const SCOPE_TYPE_DEFAULT = 'default';
    /**
     * Retrieve config value by path and scope.
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|int|string|\Magento\Framework\App\ScopeInterface $scopeCode
     * @return mixed
     */
    public function get_value($path, $scope_type = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_code = null);
    /**
     * Retrieve config flag by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|int|string|\Magento\Framework\App\ScopeInterface $scopeCode
     * @return bool
     */
    public function is_set_flag($path, $scope_type = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_code = null);
}