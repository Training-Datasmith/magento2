<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * AdminNotification observer
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PredispatchAdminActionControllerObserver implements ObserverInterface
{
    /**
     * @var \Magento\AdminNotification\Model\FeedFactory
     */
    protected $_feedFactory;

    public function __construct(
        \Magento\AdminNotification\Model\FeedFactory $feedFactory,
        protected \Magento\Backend\Model\Auth\Session $_backendAuthSession
    ) {
        $this->_feedFactory = $feedFactory;
    }

    /**
     * Predispatch admin action controller
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            $feedModel = $this->_feedFactory->create();
            /* @var $feedModel \Magento\AdminNotification\Model\Feed */
            $feedModel->checkUpdate();
        }
    }
}
