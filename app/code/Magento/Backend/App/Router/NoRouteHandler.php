<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App\Router;

/**
 * @api
 * @since 100.0.2
 */
class No_Route_Handler implements \Magento\Framework\App\Router\No_Route_Handler_Interface
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\App\Route\ConfigInterface
     */
    protected $route_config;
    /**
     * @param \Magento\Backend\Helper\Data $helper
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     */
    public function __construct(\Magento\Backend\Helper\Data $helper, \Magento\Framework\App\Route\Config_Interface $route_config)
    {
        $this->helper = $helper;
        $this->route_config = $route_config;
    }
    /**
     * Check and process no route request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function process(\Magento\Framework\App\Request_Interface $request)
    {
        $request_path_params = explode('/', trim($request->get_path_info(), '/'));
        $area_front_name = array_shift($request_path_params);
        if ($area_front_name === $this->helper->get_area_front_name(true)) {
            $module_name = $this->route_config->get_route_front_name('adminhtml');
            $action_namespace = 'noroute';
            $action_name = 'index';
            $request->set_module_name($module_name)->set_controller_name($action_namespace)->set_action_name($action_name);
            return true;
        }
        return false;
    }
}