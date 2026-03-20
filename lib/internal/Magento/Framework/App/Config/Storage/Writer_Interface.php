<?php

declare (strict_types=1);
/**
 * Application config storage writer interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Storage;

use Magento\Framework\App\Config\Scope_Config_Interface;
/**
 * Interface \Magento\Framework\App\Config\Storage\WriterInterface
 * @api
 * @since 100.0.2
 */
interface Writer_Interface
{
    /**
     * Delete config value from storage
     *
     * @param   string $path
     * @param   string $scope
     * @param   int $scopeId
     * @return void
     */
    public function delete($path, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_id = 0);
    /**
     * Save config value to storage
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    public function save($path, $value, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_id = 0);
}