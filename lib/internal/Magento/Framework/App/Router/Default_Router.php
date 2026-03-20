<?php

declare (strict_types=1);
/**
 * Default application router
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Router;

use Magento\Framework\App\Action_Factory;
use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\App\Router_Interface;
class Default_Router implements Router_Interface
{
    /**
     * @var NoRouteHandlerList
     */
    protected $no_route_handler_list;
    /**
     * @var ActionFactory
     */
    protected $action_factory;
    /**
     * @param ActionFactory $actionFactory
     * @param NoRouteHandlerList $noRouteHandlerList
     */
    public function __construct(Action_Factory $action_factory, No_Route_Handler_List $no_route_handler_list)
    {
        $this->action_factory = $action_factory;
        $this->no_route_handler_list = $no_route_handler_list;
    }
    /**
     * Modify request and set to no-route action
     *
     * @param RequestInterface $request
     * @return ActionInterface
     */
    public function match(Request_Interface $request)
    {
        foreach ($this->no_route_handler_list->get_handlers() as $no_route_handler) {
            if ($no_route_handler->process($request)) {
                break;
            }
        }
        return $this->action_factory->create(\Magento\Framework\App\Action\Forward::class);
    }
}