<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\Action_Flag;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\App\Response\Redirect_Interface;
use Magento\Framework\App\Response_Interface;
use Magento\Framework\App\View_Interface;
use Magento\Framework\Event\Manager_Interface as EventManagerInterface;
use Magento\Framework\Exception\Not_Found_Exception;
use Magento\Framework\Message\Manager_Interface as MessageManagerInterface;
use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Profiler;
use Magento\Framework\Url_Interface;
/**
 * Extend from this class to create actions controllers in frontend area of your application.
 * It contains standard action behavior (event dispatching, flag checks)
 * Action classes that do not extend from this class will lose this behavior and might not function correctly
 *
 * @deprecated 103.0.0 Inheritance in controllers should be avoided in favor of composition
 * @see \Magento\Framework\App\ActionInterface
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
abstract class Action extends Abstract_Action
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * Namespace for session.
     * Should be defined for proper working session.
     *
     * @var string
     */
    protected $_session_namespace;
    /**
     * @var EventManagerInterface
     */
    protected $_event_manager;
    /**
     * @var ActionFlag
     */
    protected $_action_flag;
    /**
     * @var RedirectInterface
     */
    protected $_redirect;
    /**
     * @var ViewInterface
     */
    protected $_view;
    /**
     * @var UrlInterface
     */
    protected $_url;
    /**
     * @var MessageManagerInterface
     */
    protected $message_manager;
    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->_object_manager = $context->get_object_manager();
        $this->_event_manager = $context->get_event_manager();
        $this->_url = $context->get_url();
        $this->_action_flag = $context->get_action_flag();
        $this->_redirect = $context->get_redirect();
        $this->_view = $context->get_view();
        $this->message_manager = $context->get_message_manager();
    }
    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(Request_Interface $request)
    {
        $this->_request = $request;
        $profiler_key = 'CONTROLLER_ACTION:' . $request->get_full_action_name();
        Profiler::start($profiler_key);
        $result = null;
        if ($request->is_dispatched() && !$this->_action_flag->get('', self::FLAG_NO_DISPATCH)) {
            Profiler::start('action_body');
            $result = $this->execute();
            Profiler::stop('action_body');
        }
        Profiler::stop($profiler_key);
        return $result ?: $this->_response;
    }
    /**
     * Throw control to different action (control and module if was specified).
     *
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @param array|null $params
     * @return void
     */
    protected function _forward($action, $controller = null, $module = null, ?array $params = null)
    {
        $request = $this->get_request();
        $request->init_forward();
        if (isset($params)) {
            $request->set_params($params);
        }
        if (isset($controller)) {
            $request->set_controller_name($controller);
            // Module should only be reset if controller has been specified
            if (isset($module)) {
                $request->set_module_name($module);
            }
        }
        $request->set_action_name($action);
        $request->set_dispatched(false);
    }
    /**
     * Set redirect into response
     *
     * @param string $path
     * @param array $arguments
     * @return ResponseInterface
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->_redirect->redirect($this->get_response(), $path, $arguments);
        return $this->get_response();
    }
    /**
     * Returns ActionFlag value
     *
     * @return \Magento\Framework\App\ActionFlag
     */
    public function get_action_flag()
    {
        return $this->_action_flag;
    }
}