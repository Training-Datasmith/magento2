<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\Observer_Interface;
use Magento\Framework\Exception\Plugin\Authentication_Exception as PluginAuthenticationException;
class Check_User_Login_Backend_Observer implements Observer_Interface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;
    /**
     * @var CaptchaStringResolver
     */
    protected $captcha_string_resolver;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param CaptchaStringResolver $captchaStringResolver
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(\Magento\Captcha\Helper\Data $helper, Captcha_String_Resolver $captcha_string_resolver, \Magento\Framework\App\Request_Interface $request)
    {
        $this->_helper = $helper;
        $this->captcha_string_resolver = $captcha_string_resolver;
        $this->_request = $request;
    }
    /**
     * Check Captcha On User Login Backend Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\Plugin\AuthenticationException
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form_id = 'backend_login';
        $captcha_model = $this->_helper->get_captcha($form_id);
        $login = $observer->get_event()->get_username();
        if ($captcha_model->is_required($login) && !$captcha_model->is_correct($this->captcha_string_resolver->resolve($this->_request, $form_id))) {
            $captcha_model->log_attempt($login);
            throw new Plugin_Authentication_Exception(__('Incorrect CAPTCHA.'));
        }
        $captcha_model->log_attempt($login);
        return $this;
    }
}