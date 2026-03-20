<?php

declare (strict_types=1);
/**
 * Application configuration object. Used to access configuration when application is installed.
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Config\Mutable_Scope_Config_Interface;
use Magento\Framework\App\Config\Scope_Config_Interface;
/**
 * @inheritdoc
 */
class Mutable_Scope_Config extends Config implements Mutable_Scope_Config_Interface
{
    /**
     * @var array
     */
    private $data;
    /**
     * @inheritdoc
     */
    public function get_value($path = null, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_code = null)
    {
        if (isset($this->data[$scope][$scope_code][$path])) {
            return $this->data[$scope][$scope_code][$path];
        }
        return parent::get_value($path, $scope, $scope_code);
    }
    /**
     * Set config value in the corresponding config scope
     *
     * @param string $path
     * @param mixed $value
     * @param string $scope
     * @param null|string $scopeCode
     * @return void
     */
    public function set_value($path, $value, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_code = null)
    {
        $this->data[$scope][$scope_code ?? ''][$path] = $value;
    }
    /**
     * @inheritdoc
     */
    public function clean()
    {
        $this->data = null;
        parent::clean();
    }
}