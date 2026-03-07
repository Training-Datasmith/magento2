<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Block;

/**
 * Toolbar entry that shows latest notifications
 *
 * @api
 * @since 100.0.2
 */
class ToolbarEntry extends \Magento\Backend\Block\Template
{
    /**
     * Number of notifications showed on expandable window
     */
    public const NOTIFICATIONS_NUMBER = 3;

    /**
     * Number of notifications showed on icon
     */
    public const NOTIFICATIONS_COUNTER_MAX = 99;

    /**
     * Length of notification description showed by default
     */
    public const NOTIFICATION_DESCRIPTION_LENGTH = 150;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        /**
         * Collection of latest unread notifications
         */
        protected \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Unread $_notificationList,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Retrieve notification description start length
     */
    public function getNotificationDescriptionLength(): int
    {
        return self::NOTIFICATION_DESCRIPTION_LENGTH;
    }

    /**
     * Retrieve notification counter max value
     */
    public function getNotificationCounterMax(): int
    {
        return self::NOTIFICATIONS_COUNTER_MAX;
    }

    /**
     * Retrieve number of unread notifications
     *
     * @return int
     */
    public function getUnreadNotificationCount()
    {
        return $this->_notificationList->getSize();
    }

    /**
     * Retrieve the list of latest unread notifications
     *
     * @return \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
     */
    public function getLatestUnreadNotifications()
    {
        return $this->_notificationList->setPageSize(self::NOTIFICATIONS_NUMBER);
    }

    /**
     * Format notification date (show only time if notification has been added today)
     *
     * @param string $dateString
     * @return string
     */
    public function formatNotificationDate($dateString)
    {
        $date = new \DateTime($dateString);
        if ($date == new \DateTime('today')) {
            return $this->_localeDate->formatDateTime(
                $date,
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::SHORT
            );
        }
        return $this->_localeDate->formatDateTime(
            $date,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
    }
}
