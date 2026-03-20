<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * Config scope interface.
 *
 * @api
 * @since 100.0.2
 */
interface Scope_Interface
{
    /**
     * Get current configuration scope identifier
     *
     * @return string
     */
    public function get_current_scope();
    /**
     * Set current configuration scope
     *
     * @param string $scope
     * @return void
     */
    public function set_current_scope($scope);
}