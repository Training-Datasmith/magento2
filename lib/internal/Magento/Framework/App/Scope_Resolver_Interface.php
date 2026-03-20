<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

/**
 * Scopes provider interface
 *
 * @api
 */
interface Scope_Resolver_Interface
{
    /**
     * Retrieve application scope object
     *
     * @param null|int $scopeId
     * @return \Magento\Framework\App\ScopeInterface
     */
    public function get_scope($scope_id = null);
    /**
     * Retrieve scopes array
     *
     * @return \Magento\Framework\App\ScopeInterface[]
     */
    public function get_scopes();
}