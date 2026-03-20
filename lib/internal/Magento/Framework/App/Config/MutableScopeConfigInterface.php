<?php

declare (strict_types=1);
/**
 * Configuration interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

/**
 * @api
 * @since 100.0.2
 */
interface Mutable_Scope_Config_Interface extends \Magento\Framework\App\Config\Scope_Config_Interface
{
    /**
     * Set config value in the corresponding config scope
     *
     * @param string $path
     * @param mixed $value
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return void
     */
    public function set_value($path, $value, $scope_type = \Magento\Framework\App\Config\Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_code = null);
}