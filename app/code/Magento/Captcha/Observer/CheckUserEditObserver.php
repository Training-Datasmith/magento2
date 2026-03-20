<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Observer;

use Magento\Customer\Model\Authentication_Interface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\Event\Observer_Interface;
/**
 * Class CheckUserEditObserver
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Check_User_Edit_Observer implements Observer_Interface
{
    public const FORM_ID = 'user_edit';
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $action_flag;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $message_manager;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;
    /**
     * @var CaptchaStringResolver
     */
    protected $captcha_string_resolver;
    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authentication;
    /**
     * @var Session
     */
    protected $customer_session;
    /**
     * @var ScopeConfigInterface
     */
    protected $scope_config;
    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param CaptchaStringResolver $captchaStringResolver
     * @param AuthenticationInterface $authentication
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Captcha\Helper\Data $helper, \Magento\Framework\App\Action_Flag $action_flag, \Magento\Framework\Message\Manager_Interface $message_manager, \Magento\Framework\App\Response\Redirect_Interface $redirect, Captcha_String_Resolver $captcha_string_resolver, Authentication_Interface $authentication, Session $customer_session, Scope_Config_Interface $scope_config)
    {
        $this->helper = $helper;
        $this->action_flag = $action_flag;
        $this->message_manager = $message_manager;
        $this->redirect = $redirect;
        $this->captcha_string_resolver = $captcha_string_resolver;
        $this->authentication = $authentication;
        $this->customer_session = $customer_session;
        $this->scope_config = $scope_config;
    }
    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\SessionException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $captcha_model = $this->helper->get_captcha(self::FORM_ID);
        if ($captcha_model->is_required()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->get_controller_action();
            if (!$captcha_model->is_correct($this->captcha_string_resolver->resolve($controller->get_request(), self::FORM_ID))) {
                $customer_id = $this->customer_session->get_customer_id();
                $this->authentication->process_authentication_failure($customer_id);
                if ($this->authentication->is_locked($customer_id)) {
                    $this->customer_session->logout();
                    $this->customer_session->start();
                    $message = __('The account is locked. Please wait and try again or contact %1.', $this->scope_config->get_value('contact/email/recipient_email'));
                    $this->message_manager->add_error_message($message);
                }
                $this->message_manager->add_error_message(__('Incorrect CAPTCHA'));
                $this->action_flag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->get_response(), '*/*/edit');
            }
        }
        $customer = $this->customer_session->get_customer();
        $login = $customer->get_email();
        $captcha_model->log_attempt($login);
        return $this;
    }
}