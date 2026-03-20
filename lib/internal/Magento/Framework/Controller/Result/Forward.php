<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Controller\Result;

use Magento\Framework\App\Request_Interface;
use Magento\Framework\App\Response\Http_Interface as HttpResponseInterface;
use Magento\Framework\Controller\Abstract_Result;
/**
 * Forward Controller Result
 *
 * @api
 */
class Forward extends Abstract_Result
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var string
     */
    protected $module;
    /**
     * @var string
     */
    protected $controller;
    /**
     * @var array
     */
    protected $params = [];
    /**
     * @param RequestInterface $request
     */
    public function __construct(Request_Interface $request)
    {
        $this->request = $request;
    }
    /**
     * @param string $module
     * @return $this
     */
    public function set_module($module)
    {
        $this->module = $module;
        return $this;
    }
    /**
     * @param string $controller
     * @return $this
     */
    public function set_controller($controller)
    {
        $this->controller = $controller;
        return $this;
    }
    /**
     * @param array $params
     * @return $this
     */
    public function set_params(array $params)
    {
        $this->params = $params;
        return $this;
    }
    /**
     * @param string $action
     * @return $this
     */
    public function forward($action)
    {
        $this->request->init_forward();
        if (!empty($this->params)) {
            $this->request->set_params($this->params);
        }
        if (!empty($this->controller)) {
            $this->request->set_controller_name($this->controller);
            // Module should only be reset if controller has been specified
            if (!empty($this->module)) {
                $this->request->set_module_name($this->module);
            }
        }
        $this->request->set_action_name($action);
        $this->request->set_dispatched(false);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function render(Http_Response_Interface $response)
    {
        return $this;
    }
}