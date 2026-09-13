<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\App\Action;

use Magento\Framework\Controller\ResultFactory;

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
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var bool
     */
    protected $_canUseBaseUrl;

    /**
     * @param bool $_canUseBaseUrl
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        ResultFactory $resultFactory,
        \Magento\Backend\Model\Session $_session,
        \Magento\Framework\AuthorizationInterface $_authorization,
        \Magento\Backend\Model\Auth $_auth,
        \Magento\Backend\Helper\Data $_helper,
        \Magento\Backend\Model\UrlInterface $_backendUrl,
        \Magento\Framework\Data\Form\FormKey\Validator $_formKeyValidator,
        \Magento\Framework\Locale\ResolverInterface $_localeResolver,
        $_canUseBaseUrl = false
    ) {
        parent::__construct(
            $request,
            $response,
            $objectManager,
            $eventManager,
            $url,
            $redirect,
            $actionFlag,
            $view,
            $messageManager,
            $resultRedirectFactory,
            $resultFactory
        );
        $this->_session = $_session;
        $this->_authorization = $_authorization;
        $this->_auth = $_auth;
        $this->_helper = $_helper;
        $this->_backendUrl = $_backendUrl;
        $this->_formKeyValidator = $_formKeyValidator;
        $this->_localeResolver = $_localeResolver;
        $this->_canUseBaseUrl = $_canUseBaseUrl;
    }

    /**
     * @return \Magento\Backend\Model\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @return \Magento\Framework\AuthorizationInterface
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }

    /**
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function getBackendUrl()
    {
        return $this->_backendUrl;
    }

    /**
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCanUseBaseUrl()
    {
        return $this->_canUseBaseUrl;
    }

    /**
     * @return \Magento\Framework\Data\Form\FormKey\Validator
     */
    public function getFormKeyValidator()
    {
        return $this->_formKeyValidator;
    }

    /**
     * @return \Magento\Backend\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @return \Magento\Framework\Locale\ResolverInterface
     */
    public function getLocaleResolver()
    {
        return $this->_localeResolver;
    }

    /**
     * @return \Magento\Backend\Model\Session
     */
    public function getSession()
    {
        return $this->_session;
    }
}
