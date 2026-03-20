<?php

declare (strict_types=1);
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Plugin;

use Magento\Captcha\Helper\Data as HelperCaptcha;
use Magento\Captcha\Model\Resource_Model\Log_Factory;
use Magento\Sales\Api\Data\Order_Interface;
use Magento\Sales\Api\Order_Management_Interface;
/**
 * Reset attempts for frontend checkout
 */
class Reset_Payment_Attempts_After_Order_Is_Placed_Plugin
{
    /**
     * Form ID
     */
    private const FORM_ID = 'payment_processing_request';
    /**
     * @var HelperCaptcha
     */
    private $helper;
    /**
     * @var LogFactory
     */
    private $res_log_factory;
    /**
     * ResetPaymentAttemptsAfterOrderIsPlacedPlugin constructor
     *
     * @param HelperCaptcha $helper
     * @param LogFactory $resLogFactory
     */
    public function __construct(Helper_Captcha $helper, Log_Factory $res_log_factory)
    {
        $this->helper = $helper;
        $this->res_log_factory = $res_log_factory;
    }
    /**
     * Reset attempts for frontend checkout
     *
     * @param OrderManagementInterface $subject
     * @param OrderInterface $result
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function after_place(Order_Management_Interface $subject, Order_Interface $result, Order_Interface $order): Order_Interface
    {
        $captcha_model = $this->helper->get_captcha(self::FORM_ID);
        $captcha_model->set_show_captcha_in_session(false);
        $this->res_log_factory->create()->delete_user_attempts($order->get_customer_email());
        return $result;
    }
}