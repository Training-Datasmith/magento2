<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Store\Model\Scope_Interface;
/**
 * Configures full path for configurations, including scope data and configuration type.
 */
class Config_Path_Resolver
{
    /**
     * @var ScopeCodeResolver
     */
    private $scope_code_resolver;
    /**
     * @param ScopeCodeResolver $scopeCodeResolver
     */
    public function __construct(Scope_Code_Resolver $scope_code_resolver)
    {
        $this->scope_code_resolver = $scope_code_resolver;
    }
    /**
     * Creates full config path for given params.
     *
     * If $type variable was provided, it will be used as first part of path.
     *
     * @param string $path The path of configuration
     * @param string $scope The scope of configuration
     * @param string|int|null $scopeCode The scope code or its identifier. The values for this
     * field are taken from 'store' or 'store_website' tables, depends on $scope value
     * @param string|null $type The type of configuration.
     * The available types are declared in implementations of Magento\Framework\App\Config\ConfigTypeInterface
     * E.g.
     * ```php
     * const CONFIG_TYPE = 'system';
     * ```
     * @return string Resolved configuration path
     */
    public function resolve($path, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_code = null, $type = null)
    {
        $path = $path !== null ? trim($path, '/') : '';
        $scope = $scope !== null ? rtrim($scope, 's') : '';
        /** Scope name is currently stored in plural form. */
        if (in_array($scope, [Scope_Interface::SCOPE_STORE, Scope_Interface::SCOPE_WEBSITE])) {
            $scope .= 's';
        }
        $scope_path = $type ? $type . '/' . $scope : $scope;
        if ($scope !== Scope_Config_Interface::SCOPE_TYPE_DEFAULT) {
            if (is_numeric($scope_code) || $scope_code === null) {
                $scope_code = $this->scope_code_resolver->resolve($scope, $scope_code);
            }
            $scope_path .= '/' . $scope_code;
        }
        return $scope_path . ($path ? '/' . $path : '');
    }
}