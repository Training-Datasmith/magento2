<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model;

/**
 * Notification service model
 *
 * @api
 * @since 100.0.2
 */
class Notification_Service
{
    public function __construct(protected \Magento\Admin_Notification\Model\Inbox_Factory $_notification_factory)
    {
    }
    /**
     * Mark notification as read
     *
     * @param int $notificationId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function mark_as_read($notification_id): void
    {
        $notification = $this->_notification_factory->create();
        $notification->load($notification_id);
        if (!$notification->get_id()) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('Wrong notification ID specified.'));
        }
        $notification->set_is_read(1);
        $notification->save();
    }
}