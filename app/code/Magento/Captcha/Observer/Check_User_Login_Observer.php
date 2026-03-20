<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Observer;

use Magento\Customer\Api\Customer_Repository_Interface;
use Magento\Customer\Model\Authentication_Interface;
use Magento\Framework\Event\Observer_Interface;
use Magento\Framework\Exception\No_Such_Entity_Exception;
/**
 * Check captcha on user login page observer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Check_User_Login_Observer implements Observer_Interface
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
     * @var CaptchaStringResolver
     */
    protected $captcha_string_resolver;
    /**
     * Customer data
     *
     * @var \Magento\Customer\Model\Url
     */
    protected $_customer_url;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer_repository;
    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authentication;
    /**
     * CheckUserLoginObserver constructor.
     *
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Session\SessionManagerInterface $customerSession
     * @param CaptchaStringResolver $captchaStringResolver
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(\Magento\Captcha\Helper\Data $helper, \Magento\Framework\App\Action_Flag $action_flag, \Magento\Framework\Message\Manager_Interface $message_manager, \Magento\Framework\Session\Session_Manager_Interface $customer_session, Captcha_String_Resolver $captcha_string_resolver, \Magento\Customer\Model\Url $customer_url)
    {
        $this->_helper = $helper;
        $this->_action_flag = $action_flag;
        $this->message_manager = $message_manager;
        $this->_session = $customer_session;
        $this->captcha_string_resolver = $captcha_string_resolver;
        $this->_customer_url = $customer_url;
    }
    /**
     * Get customer repository
     *
     * @return \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private function get_customer_repository()
    {
        if (!$this->customer_repository instanceof \Magento\Customer\Api\Customer_Repository_Interface) {
            return \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Customer\Api\Customer_Repository_Interface::class);
        } else {
            return $this->customer_repository;
        }
    }
    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function get_authentication()
    {
        if (!$this->authentication instanceof Authentication_Interface) {
            return \Magento\Framework\App\Object_Manager::get_instance()->get(Authentication_Interface::class);
        } else {
            return $this->authentication;
        }
    }
    /**
     * Check captcha on user login page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form_id = 'user_login';
        $captcha_model = $this->_helper->get_captcha($form_id);
        $controller = $observer->get_controller_action();
        $login_params = $controller->get_request()->get_post('login');
        $login = is_array($login_params) && array_key_exists('username', $login_params) ? $login_params['username'] : null;
        if ($captcha_model->is_required($login)) {
            $word = $this->captcha_string_resolver->resolve($controller->get_request(), $form_id);
            if (!$captcha_model->is_correct($word)) {
                try {
                    $customer = $this->get_customer_repository()->get($login);
                    $this->get_authentication()->process_authentication_failure($customer->get_id());
                    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
                } catch (No_Such_Entity_Exception $e) {
                    //do nothing as customer existence is validated later in authenticate method
                }
                $this->message_manager->add_error_message(__('Incorrect CAPTCHA'));
                $this->_action_flag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->_session->set_username($login);
                $before_url = $this->_session->get_before_auth_url();
                $url = $before_url ? $before_url : $this->_customer_url->get_login_url();
                $controller->get_response()->set_redirect($url);
            }
        }
        $captcha_model->log_attempt($login);
        return $this;
    }
}