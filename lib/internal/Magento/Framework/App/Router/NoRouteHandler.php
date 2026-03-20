<?php

declare (strict_types=1);
/**
 * Default no route handler
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Router;

class No_Route_Handler implements \Magento\Framework\App\Router\No_Route_Handler_Interface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Config\Scope_Config_Interface $config)
    {
        $this->_config = $config;
    }
    /**
     * Check and process no route request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function process(\Magento\Framework\App\Request_Interface $request)
    {
        $no_route_path = $this->_config->get_value('web/default/no_route', 'default');
        if ($no_route_path) {
            $no_route = explode('/', $no_route_path);
        } else {
            $no_route = [];
        }
        $module_name = isset($no_route[0]) ? $no_route[0] : 'core';
        $action_path = isset($no_route[1]) ? $no_route[1] : 'index';
        $action_name = isset($no_route[2]) ? $no_route[2] : 'index';
        $request->set_module_name($module_name)->set_controller_name($action_path)->set_action_name($action_name);
        return true;
    }
}