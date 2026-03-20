<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Captcha\Observer;

use Magento\Captcha\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action_Flag;
use Magento\Framework\App\Request\Data_Persistor_Interface;
use Magento\Framework\App\Response\Redirect_Interface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\Observer_Interface;
use Magento\Framework\Message\Manager_Interface;
/**
 * Check captcha on contact us form submit observer.
 */
class Check_Contact_Us_Form_Observer implements Observer_Interface
{
    /**
     * @var Data
     */
    protected $_helper;
    /**
     * @var ActionFlag
     */
    protected $_action_flag;
    /**
     * @var ManagerInterface
     */
    protected $message_manager;
    /**
     * @var RedirectInterface
     */
    protected $redirect;
    /**
     * @var CaptchaStringResolver
     */
    protected $captcha_string_resolver;
    /**
     * @var DataPersistorInterface
     */
    private $data_persistor;
    /**
     * @param Data $helper
     * @param ActionFlag $actionFlag
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param CaptchaStringResolver $captchaStringResolver
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(Data $helper, Action_Flag $action_flag, Manager_Interface $message_manager, Redirect_Interface $redirect, Captcha_String_Resolver $captcha_string_resolver, Data_Persistor_Interface $data_persistor)
    {
        $this->_helper = $helper;
        $this->_action_flag = $action_flag;
        $this->message_manager = $message_manager;
        $this->redirect = $redirect;
        $this->captcha_string_resolver = $captcha_string_resolver;
        $this->data_persistor = $data_persistor;
    }
    /**
     * Check CAPTCHA on Contact Us page
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $form_id = 'contact_us';
        $captcha = $this->_helper->get_captcha($form_id);
        if ($captcha->is_required()) {
            /** @var Action $controller */
            $controller = $observer->get_controller_action();
            if (!$captcha->is_correct($this->captcha_string_resolver->resolve($controller->get_request(), $form_id))) {
                $this->message_manager->add_error_message(__('Incorrect CAPTCHA.'));
                $this->data_persistor->set($form_id, $controller->get_request()->get_post_value());
                $this->_action_flag->set('', Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->get_response(), 'contact/index/index');
            }
        }
    }
}