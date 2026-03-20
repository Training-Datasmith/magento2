<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\ScopeValidatorInterface
 *
 * @api
 */
interface Scope_Validator_Interface
{
    /**
     * Check that scope and scope id is exists
     *
     * @param string $scope
     * @param string $scopeId
     * @return bool
     */
    public function is_valid_scope($scope, $scope_id = null);
}