<?php

declare (strict_types=1);
/**
 * Application config storage writer
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Storage;

use Magento\Framework\App\Config\Scope_Config_Interface;
class Writer implements \Magento\Framework\App\Config\Storage\Writer_Interface
{
    /**
     * Resource model of config data
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $_resource;
    /**
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resource
     */
    public function __construct(\Magento\Framework\App\Config\Config_Resource\Config_Interface $resource)
    {
        $this->_resource = $resource;
    }
    /**
     * Delete config value from storage
     *
     * @param   string $path
     * @param   string $scope
     * @param   int $scopeId
     * @return  void
     */
    public function delete($path, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_id = 0)
    {
        $this->_resource->delete_config(rtrim($path, '/'), $scope, $scope_id);
    }
    /**
     * Save config value to storage
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    public function save($path, $value, $scope = Scope_Config_Interface::SCOPE_TYPE_DEFAULT, $scope_id = 0)
    {
        $this->_resource->save_config(rtrim($path, '/'), $value, $scope, $scope_id);
    }
}