<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Config\Config_Resource;

use Magento\Framework\App\Config\Scope_Config_Interface;
/**
 * Resource for storing store configuration values
 *
 * @api
 */
interface Config_Interface
{
    /**
     * Save config value to the storage resource
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function save_config($path, $value, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_id = 0);
    /**
     * Delete config value from the storage resource
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function delete_config($path, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_id = 0);
}