<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\Exception\Not_Found_Exception;
use Magento\Framework\Interception\Interceptor_Interface;
use Magento\Framework\Phrase;
use Psr\Log\Logger_Interface;
/**
 * Make sure that a request's method can be processed by an action.
 */
class Http_Method_Validator implements Validator_Interface
{
    /**
     * @var HttpMethodMap
     */
    private $map;
    /**
     * @var LoggerInterface
     */
    private $log;
    /**
     * @param HttpMethodMap $map
     * @param LoggerInterface $logger
     */
    public function __construct(Http_Method_Map $map, Logger_Interface $logger)
    {
        $this->map = $map;
        $this->log = $logger;
    }
    /**
     * Create exception when invalid HTTP method used.
     *
     * @param Http $request
     * @param ActionInterface $action
     * @throws InvalidRequestException
     *
     * @return void
     */
    private function throw_exception(Http $request, Action_Interface $action): void
    {
        $uri = $request->get_request_uri();
        $method = $request->get_method();
        if ($action instanceof Interceptor_Interface) {
            $action_class = get_parent_class($action);
        } else {
            $action_class = get_class($action);
        }
        $this->log->debug("URI '{$uri}'' cannot be accessed with {$method} method ({$action_class})");
        throw new Invalid_Request_Exception(new Not_Found_Exception(new Phrase('Page not found.')));
    }
    /**
     * @inheritDoc
     */
    public function validate(Request_Interface $request, Action_Interface $action): void
    {
        if ($request instanceof Http) {
            $method = $request->get_method();
            $map = $this->map->get_map();
            //If we don't have an interface for the HTTP method or
            //the action has HTTP method limitations and doesn't allow the
            //received one then the request is invalid.
            if (!array_key_exists($method, $map) || array_intersect($map, class_implements($action, true)) && !$action instanceof $map[$method]) {
                $this->throw_exception($request, $action);
            }
        }
    }
}