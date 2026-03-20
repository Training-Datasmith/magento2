<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\Observer_Interface;
class Reset_Attempt_For_Frontend_Account_Edit_Observer implements Observer_Interface
{
    /**
     * Form ID
     */
    public const FORM_ID = 'user_edit';
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Captcha\Model\ResourceModel\LogFactory
     */
    public $res_log_factory;
    /**
     * ResetAttemptForFrontendAccountEditObserver constructor
     *
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
     */
    public function __construct(\Magento\Captcha\Helper\Data $helper, \Magento\Captcha\Model\Resource_Model\Log_Factory $res_log_factory)
    {
        $this->helper = $helper;
        $this->res_log_factory = $res_log_factory;
    }
    /**
     * Reset Attempts For Frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Captcha\Observer\ResetAttemptForFrontendObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $email = $observer->get_email();
        $captcha_model = $this->helper->get_captcha(self::FORM_ID);
        $captcha_model->set_show_captcha_in_session(false);
        return $this->res_log_factory->create()->delete_user_attempts($email);
    }
}