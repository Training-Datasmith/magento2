<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Observer;

use Magento\Captcha\Model\Resource_Model\Log_Factory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\Observer_Interface;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Reset captcha attempts for Backend
 */
class Reset_Attempt_For_Backend_Observer implements Observer_Interface
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
     * Reset Attempts For Backend
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $this->res_log_factory->create()->delete_user_attempts($observer->get_user()->get_username());
    }
}