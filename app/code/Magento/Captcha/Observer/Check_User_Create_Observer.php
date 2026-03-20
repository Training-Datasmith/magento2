<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\Observer_Interface;
/**
 * Class CheckUserCreateObserver
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Check_User_Create_Observer implements Observer_Interface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_action_flag;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $message_manager;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;
    /**
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url_manager;
    /**
     * @var CaptchaStringResolver
     */
    protected $captcha_string_resolver;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;
    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param CaptchaStringResolver $captchaStringResolver
     */
    public function __construct(\Magento\Captcha\Helper\Data $helper, \Magento\Framework\App\Action_Flag $action_flag, \Magento\Framework\Message\Manager_Interface $message_manager, \Magento\Framework\Session\Session_Manager_Interface $session, \Magento\Framework\Url_Interface $url_manager, \Magento\Framework\App\Response\Redirect_Interface $redirect, Captcha_String_Resolver $captcha_string_resolver)
    {
        $this->_helper = $helper;
        $this->_action_flag = $action_flag;
        $this->message_manager = $message_manager;
        $this->_session = $session;
        $this->_url_manager = $url_manager;
        $this->redirect = $redirect;
        $this->captcha_string_resolver = $captcha_string_resolver;
    }
    /**
     * Check Captcha On User Login Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form_id = 'user_create';
        $captcha_model = $this->_helper->get_captcha($form_id);
        if ($captcha_model->is_required()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->get_controller_action();
            if (!$captcha_model->is_correct($this->captcha_string_resolver->resolve($controller->get_request(), $form_id))) {
                $this->message_manager->add_error_message(__('Incorrect CAPTCHA'));
                $this->_action_flag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->_session->set_customer_form_data($controller->get_request()->get_post_value());
                $url = $this->_url_manager->get_url('*/*/create', ['_nosecret' => true]);
                $controller->get_response()->set_redirect($this->redirect->error($url));
            }
        }
        return $this;
    }
}