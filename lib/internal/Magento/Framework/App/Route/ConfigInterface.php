<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Route;

/**
 * Routes configuration interface
 *
 * @api
 * @since 100.0.2
 */
interface Config_Interface
{
    /**
     * Retrieve route front name
     *
     * @param string $routeId
     * @param string $scope
     * @return string
     */
    public function get_route_front_name($route_id, $scope = null);
    /**
     * Get route id by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return string
     */
    public function get_route_by_front_name($front_name, $scope = null);
    /**
     * Retrieve list of modules by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return string[]
     */
    public function get_modules_by_front_name($front_name, $scope = null);
}