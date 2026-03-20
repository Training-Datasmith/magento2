<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\Observer_Interface;
/**
 * Class CheckForgotpasswordObserver
 */
class Check_Forgotpassword_Observer implements Observer_Interface
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
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;
    /**
     * @var CaptchaStringResolver
     */
    protected $captcha_string_resolver;
    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param CaptchaStringResolver $captchaStringResolver
     */
    public function __construct(\Magento\Captcha\Helper\Data $helper, \Magento\Framework\App\Action_Flag $action_flag, \Magento\Framework\Message\Manager_Interface $message_manager, \Magento\Framework\App\Response\Redirect_Interface $redirect, Captcha_String_Resolver $captcha_string_resolver)
    {
        $this->_helper = $helper;
        $this->_action_flag = $action_flag;
        $this->message_manager = $message_manager;
        $this->redirect = $redirect;
        $this->captcha_string_resolver = $captcha_string_resolver;
    }
    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form_id = 'user_forgotpassword';
        $captcha_model = $this->_helper->get_captcha($form_id);
        if ($captcha_model->is_required()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->get_controller_action();
            if (!$captcha_model->is_correct($this->captcha_string_resolver->resolve($controller->get_request(), $form_id))) {
                $this->message_manager->add_error_message(__('Incorrect CAPTCHA'));
                $this->_action_flag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->get_response(), '*/*/forgotpassword');
            }
        }
        return $this;
    }
}