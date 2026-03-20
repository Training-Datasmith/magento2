<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Observer;

use Magento\Framework\Event\Observer_Interface;
/**
 * AdminNotification observer
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Predispatch_Admin_Action_Controller_Observer implements Observer_Interface
{
    /**
     * @var \Magento\AdminNotification\Model\FeedFactory
     */
    protected $_feed_factory;
    public function __construct(\Magento\Admin_Notification\Model\Feed_Factory $feed_factory, protected \Magento\Backend\Model\Auth\Session $_backend_auth_session)
    {
        $this->_feed_factory = $feed_factory;
    }
    /**
     * Predispatch admin action controller
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if ($this->_backend_auth_session->is_logged_in()) {
            $feed_model = $this->_feed_factory->create();
            /* @var $feedModel \Magento\AdminNotification\Model\Feed */
            $feed_model->check_update();
        }
    }
}