<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Provider of scope resolvers by type
 */
class Scope_Resolver_Pool
{
    /**
     * @var array
     */
    protected $_scope_resolvers = [];
    /**
     * @param \Magento\Framework\App\ScopeResolverInterface[] $scopeResolvers
     */
    public function __construct(array $scope_resolvers = [])
    {
        $this->_scope_resolvers = $scope_resolvers;
    }
    /**
     * Retrieve reader by scope type
     *
     * @param string $scopeType
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\App\ScopeResolverInterface
     */
    public function get($scope_type)
    {
        if (!isset($this->_scope_resolvers[$scope_type]) || !$this->_scope_resolvers[$scope_type] instanceof \Magento\Framework\App\Scope_Resolver_Interface) {
            throw new \InvalidArgumentException("Invalid scope type '{$scope_type}'");
        }
        return $this->_scope_resolvers[$scope_type];
    }
}