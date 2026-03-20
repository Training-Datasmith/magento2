<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App\Action;

use Magento\Framework\Controller\Result_Factory;
/**
 * Constructor modification point for Magento\Backend\App\Action.
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Context extends \Magento\Framework\App\Action\Context
{
    /**
     * @param bool $_canUseBaseUrl
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\App\Request_Interface $request, \Magento\Framework\App\Response_Interface $response, \Magento\Framework\Object_Manager_Interface $object_manager, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Framework\Url_Interface $url, \Magento\Framework\App\Response\Redirect_Interface $redirect, \Magento\Framework\App\Action_Flag $action_flag, \Magento\Framework\App\View_Interface $view, \Magento\Framework\Message\Manager_Interface $message_manager, \Magento\Backend\Model\View\Result\Redirect_Factory $result_redirect_factory, Result_Factory $result_factory, protected \Magento\Backend\Model\Session $_session, protected \Magento\Framework\Authorization_Interface $_authorization, protected \Magento\Backend\Model\Auth $_auth, protected \Magento\Backend\Helper\Data $_helper, protected \Magento\Backend\Model\Url_Interface $_backend_url, protected \Magento\Framework\Data\Form\Form_Key\Validator $_form_key_validator, protected \Magento\Framework\Locale\Resolver_Interface $_locale_resolver, protected $_can_use_base_url = false)
    {
        parent::__construct($request, $response, $object_manager, $event_manager, $url, $redirect, $action_flag, $view, $message_manager, $result_redirect_factory, $result_factory);
    }
    /**
     * @return \Magento\Backend\Model\Auth
     */
    public function get_auth()
    {
        return $this->_auth;
    }
    /**
     * @return \Magento\Framework\AuthorizationInterface
     */
    public function get_authorization()
    {
        return $this->_authorization;
    }
    /**
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function get_backend_url()
    {
        return $this->_backend_url;
    }
    /**
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_can_use_base_url()
    {
        return $this->_can_use_base_url;
    }
    /**
     * @return \Magento\Framework\Data\Form\FormKey\Validator
     */
    public function get_form_key_validator()
    {
        return $this->_form_key_validator;
    }
    /**
     * @return \Magento\Backend\Helper\Data
     */
    public function get_helper()
    {
        return $this->_helper;
    }
    /**
     * @return \Magento\Framework\Locale\ResolverInterface
     */
    public function get_locale_resolver()
    {
        return $this->_locale_resolver;
    }
    /**
     * @return \Magento\Backend\Model\Session
     */
    public function get_session()
    {
        return $this->_session;
    }
}