<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Observer;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action_Flag;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\Event\Observer as Event;
use Magento\Framework\Event\Observer_Interface;
use Magento\Framework\Message\Manager_Interface;
use Magento\Framework\Session\Session_Manager_Interface;
/**
 * Handle request for Forgot Password
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Check_User_Forgot_Password_Backend_Observer implements Observer_Interface
{
    /**
     * @var CaptchaHelper
     */
    protected $_helper;
    /**
     * @var CaptchaStringResolver
     */
    protected $captcha_string_resolver;
    /**
     * @var SessionManagerInterface
     */
    protected $_session;
    /**
     * @var ActionFlag
     */
    protected $_action_flag;
    /**
     * @var ManagerInterface
     */
    protected $message_manager;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @param CaptchaHelper $helper
     * @param CaptchaStringResolver $captchaStringResolver
     * @param SessionManagerInterface $session
     * @param ActionFlag $actionFlag
     * @param ManagerInterface $messageManager
     * @param RequestInterface|null $request
     */
    public function __construct(Captcha_Helper $helper, Captcha_String_Resolver $captcha_string_resolver, Session_Manager_Interface $session, Action_Flag $action_flag, Manager_Interface $message_manager, ?Request_Interface $request = null)
    {
        $this->_helper = $helper;
        $this->captcha_string_resolver = $captcha_string_resolver;
        $this->_session = $session;
        $this->_action_flag = $action_flag;
        $this->message_manager = $message_manager;
        $this->request = $request ?? Object_Manager::get_instance()->get(Request_Interface::class);
    }
    /**
     * Check Captcha On User Login Backend Page
     *
     * @param Event $observer
     * @return $this
     * @throws \Magento\Framework\Exception\Plugin\AuthenticationException
     */
    public function execute(Event $observer)
    {
        $form_id = 'backend_forgotpassword';
        $captcha_model = $this->_helper->get_captcha($form_id);
        $controller = $observer->get_controller_action();
        $params = $this->request->get_params();
        $email = (string) $this->request->get_param('email');
        if (!empty($params) && !empty($email) && $captcha_model->is_required() && !$captcha_model->is_correct($this->captcha_string_resolver->resolve($this->request, $form_id))) {
            $this->_session->set_email($email);
            $this->_action_flag->set('', Action::FLAG_NO_DISPATCH, true);
            $this->message_manager->add_error_message(__('Incorrect CAPTCHA'));
            $controller->get_response()->set_redirect($controller->get_url('*/*/forgotpassword', ['_nosecret' => true]));
        }
        return $this;
    }
}