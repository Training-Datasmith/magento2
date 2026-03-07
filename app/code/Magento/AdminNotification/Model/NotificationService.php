<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Model;

/**
 * Notification service model
 *
 * @api
 * @since 100.0.2
 */
class NotificationService
{
    public function __construct(protected \Magento\AdminNotification\Model\InboxFactory $_notificationFactory)
    {
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function markAsRead($notificationId): void
    {
        $notification = $this->_notificationFactory->create();
        $notification->load($notificationId);
        if (!$notification->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Wrong notification ID specified.'));
        }
        $notification->setIsRead(1);
        $notification->save();
    }
}
