<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Block;

/**
 * Toolbar entry that shows latest notifications
 *
 * @api
 * @since 100.0.2
 */
class Toolbar_Entry extends \Magento\Backend\Block\Template
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
        protected \Magento\Admin_Notification\Model\Resource_Model\Inbox\Collection\Unread $_notification_list,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }
    /**
     * Retrieve notification description start length
     */
    public function get_notification_description_length(): int
    {
        return self::NOTIFICATION_DESCRIPTION_LENGTH;
    }
    /**
     * Retrieve notification counter max value
     */
    public function get_notification_counter_max(): int
    {
        return self::NOTIFICATIONS_COUNTER_MAX;
    }
    /**
     * Retrieve number of unread notifications
     *
     * @return int
     */
    public function get_unread_notification_count()
    {
        return $this->_notification_list->get_size();
    }
    /**
     * Retrieve the list of latest unread notifications
     *
     * @return \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
     */
    public function get_latest_unread_notifications()
    {
        return $this->_notification_list->set_page_size(self::NOTIFICATIONS_NUMBER);
    }
    /**
     * Format notification date (show only time if notification has been added today)
     *
     * @param string $dateString
     * @return string
     */
    public function format_notification_date($date_string)
    {
        $date = new \DateTime($date_string);
        if ($date == new \DateTime('today')) {
            return $this->_locale_date->format_date_time($date, \Intl_Date_Formatter::NONE, \Intl_Date_Formatter::SHORT);
        }
        return $this->_locale_date->format_date_time($date, \Intl_Date_Formatter::MEDIUM, \Intl_Date_Formatter::MEDIUM);
    }
}