<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Scope_Interface;
use Magento\Framework\App\Scope_Resolver_Pool;
/**
 * Class for resolving scope code
 */
class Scope_Code_Resolver
{
    /**
     * @var ScopeResolverPool
     */
    private $scope_resolver_pool;
    /**
     * @var array
     */
    private $resolved_scope_codes = [];
    /**
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(Scope_Resolver_Pool $scope_resolver_pool)
    {
        $this->scope_resolver_pool = $scope_resolver_pool;
    }
    /**
     * Resolve scope code
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function resolve($scope_type, $scope_code)
    {
        if (isset($scope_code, $this->resolved_scope_codes[$scope_type][$scope_code])) {
            return $this->resolved_scope_codes[$scope_type][$scope_code];
        }
        if ($scope_type !== Scope_Config_Interface::SCOPE_TYPE_DEFAULT) {
            $scope_resolver = $this->scope_resolver_pool->get($scope_type);
            $resolver_scope_code = $scope_resolver->get_scope($scope_code);
        } else {
            $resolver_scope_code = $scope_code;
        }
        if ($resolver_scope_code instanceof Scope_Interface) {
            $resolver_scope_code = $resolver_scope_code->get_code();
        }
        if ($scope_code === null) {
            $scope_code = $resolver_scope_code;
        }
        $this->resolved_scope_codes[$scope_type][$scope_code] = $resolver_scope_code;
        return $resolver_scope_code;
    }
    /**
     * Clean resolvedScopeCodes, store codes may have been renamed
     *
     * @return void
     */
    public function clean()
    {
        $this->resolved_scope_codes = [];
    }
}