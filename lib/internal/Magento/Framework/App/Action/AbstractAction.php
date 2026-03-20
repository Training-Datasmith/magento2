<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\Request_Interface;
use Magento\Framework\App\Response_Interface;
/**
 * Abstract redirect/forward action class
 *
 * @deprecated 103.0.0 Inheritance in controllers should be avoided in favor of composition
 * @see \Magento\Framework\App\ActionInterface
 */
abstract class Abstract_Action implements \Magento\Framework\App\Action_Interface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $result_redirect_factory;
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $result_factory;
    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->_request = $context->get_request();
        $this->_response = $context->get_response();
        $this->result_redirect_factory = $context->get_result_redirect_factory();
        $this->result_factory = $context->get_result_factory();
    }
    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    abstract public function dispatch(Request_Interface $request);
    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function get_request()
    {
        return $this->_request;
    }
    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function get_response()
    {
        return $this->_response;
    }
}