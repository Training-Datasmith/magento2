<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\Controller\Result_Factory;
/**
 * Constructor modification point for Magento\Framework\App\Action.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 *
 * @api
 * @since 100.0.2
 */
class Context implements \Magento\Framework\Object_Manager\Context_Interface
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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_event_manager;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_action_flag;
    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_view;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $message_manager;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $result_redirect_factory;
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $result_factory;
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\App\ViewInterface $view
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\App\Request_Interface $request, \Magento\Framework\App\Response_Interface $response, \Magento\Framework\Object_Manager_Interface $object_manager, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Framework\Url_Interface $url, \Magento\Framework\App\Response\Redirect_Interface $redirect, \Magento\Framework\App\Action_Flag $action_flag, \Magento\Framework\App\View_Interface $view, \Magento\Framework\Message\Manager_Interface $message_manager, \Magento\Framework\Controller\Result\Redirect_Factory $result_redirect_factory, Result_Factory $result_factory)
    {
        $this->_request = $request;
        $this->_response = $response;
        $this->_object_manager = $object_manager;
        $this->_event_manager = $event_manager;
        $this->_url = $url;
        $this->_redirect = $redirect;
        $this->_action_flag = $action_flag;
        $this->_view = $view;
        $this->message_manager = $message_manager;
        $this->result_redirect_factory = $result_redirect_factory;
        $this->result_factory = $result_factory;
    }
    /**
     * @return \Magento\Framework\App\ActionFlag
     */
    public function get_action_flag()
    {
        return $this->_action_flag;
    }
    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function get_event_manager()
    {
        return $this->_event_manager;
    }
    /**
     * @return \Magento\Framework\App\ViewInterface
     */
    public function get_view()
    {
        return $this->_view;
    }
    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function get_object_manager()
    {
        return $this->_object_manager;
    }
    /**
     * @return \Magento\Framework\App\Response\RedirectInterface
     */
    public function get_redirect()
    {
        return $this->_redirect;
    }
    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function get_request()
    {
        return $this->_request;
    }
    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function get_response()
    {
        return $this->_response;
    }
    /**
     * @return \Magento\Framework\UrlInterface
     */
    public function get_url()
    {
        return $this->_url;
    }
    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function get_message_manager()
    {
        return $this->message_manager;
    }
    /**
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function get_result_redirect_factory()
    {
        return $this->result_redirect_factory;
    }
    /**
     * @return \Magento\Framework\Controller\ResultFactory
     */
    public function get_result_factory()
    {
        return $this->result_factory;
    }
}