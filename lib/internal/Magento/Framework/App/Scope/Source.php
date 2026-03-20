<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Scope;

use Magento\Framework\App\Scope_Resolver_Pool;
use Magento\Framework\Option\Array_Interface;
class Source implements Array_Interface
{
    /**
     * @var ScopeResolverPool
     */
    protected $scope_resolver_pool;
    /**
     * @var string
     */
    protected $scope;
    /**
     * @param ScopeResolverPool $scopeResolverPool
     * @param string $scope
     */
    public function __construct(Scope_Resolver_Pool $scope_resolver_pool, $scope)
    {
        $this->scope_resolver_pool = $scope_resolver_pool;
        $this->scope = $scope;
    }
    /**
     * Return array of scope names
     *
     * @return array
     */
    public function to_option_array()
    {
        $scopes = $this->scope_resolver_pool->get($this->scope)->get_scopes();
        $array = [];
        foreach ($scopes as $scope) {
            $array[] = ['value' => $scope->get_id(), 'label' => $scope->get_name()];
        }
        return $array;
    }
}