<?php

declare (strict_types=1);
/**
 * Routes configuration converter
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Route\Config;

class Converter implements \Magento\Framework\Config\Converter_Interface
{
    /**
     * Convert config
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $routers */
        $routers = $source->get_elements_by_tag_name('router');
        /** @var \DOMNode $router */
        foreach ($routers as $router) {
            $router_config = [];
            foreach ($router->attributes as $attribute) {
                $router_config[$attribute->node_name] = $attribute->node_value;
            }
            /** @var \DOMNode $routeData */
            foreach ($router->get_elements_by_tag_name('route') as $route_data) {
                $route_config = [];
                foreach ($route_data->attributes as $route_attribute) {
                    $route_config[$route_attribute->node_name] = $route_attribute->node_value;
                }
                /** @var \DOMNode $module */
                foreach ($route_data->get_elements_by_tag_name('module') as $module_data) {
                    $module_config = [];
                    foreach ($module_data->attributes as $module_attribute) {
                        $module_config[$module_attribute->node_name] = $module_attribute->node_value;
                    }
                    $route_config['modules'][] = $module_config;
                }
                $route_config['modules'] = $this->_sort_modules_list($route_config['modules']);
                $router_config['routes'][$route_data->attributes->get_named_item('id')->node_value] = $route_config;
            }
            $output[$router->attributes->get_named_item('id')->node_value] = $router_config;
        }
        return $output;
    }
    /**
     * Sort modules list according to before/after attributes
     *
     * @param array $modulesList
     * @return array
     */
    protected function _sort_modules_list($modules_list)
    {
        $sorted_modules_list = [];
        foreach ($modules_list as $module_data) {
            if (isset($module_data['before'])) {
                $position = array_search($module_data['before'], $sorted_modules_list);
                if ($position === false) {
                    $position = 0;
                }
                array_splice($sorted_modules_list, $position, 0, $module_data['name']);
            } elseif (isset($module_data['after'])) {
                $position = array_search($module_data['after'], $sorted_modules_list);
                if ($position === false) {
                    $position = count($modules_list);
                }
                array_splice($sorted_modules_list, $position + 1, 0, $module_data['name']);
            } else {
                $sorted_modules_list[] = $module_data['name'];
            }
        }
        return $sorted_modules_list;
    }
}