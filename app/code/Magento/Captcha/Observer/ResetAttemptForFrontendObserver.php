<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Observer;

use Magento\Captcha\Model\Resource_Model\Log;
use Magento\Captcha\Model\Resource_Model\Log_Factory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\Observer_Interface;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Reset captcha attempts for Frontend
 */
class Reset_Attempt_For_Frontend_Observer implements Observer_Interface
{
    /**
     * @var LogFactory
     */
    public $res_log_factory;
    /**
     * @param LogFactory $resLogFactory
     */
    public function __construct(Log_Factory $res_log_factory)
    {
        $this->res_log_factory = $res_log_factory;
    }
    /**
     * Reset Attempts For Frontend
     *
     * @param Observer $observer
     * @return Log
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Customer $model */
        $model = $observer->get_model();
        return $this->res_log_factory->create()->delete_user_attempts($model->get_email());
    }
}