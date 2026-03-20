<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Controller\Router\Route;

use Magento\Framework\App\Router_Interface;
use Magento\Framework\Object_Manager_Interface as ObjectManager;
class Factory
{
    /**
     * @var ObjectManager
     */
    protected $object_manager;
    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(Object_Manager $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create route instance.
     *
     * @param string $routeClass
     * @param string $route Map used to match with later submitted URL path
     * @return RouterInterface
     * @throws \LogicException If specified route class does not implement proper interface.
     */
    public function create_route($route_class, $route)
    {
        $route = $this->object_manager->create($route_class, ['route' => $route]);
        if (!$route instanceof Router_Interface) {
            throw new \LogicException('Route must implement "Magento\Framework\App\RouterInterface".');
        }
        return $route;
    }
}