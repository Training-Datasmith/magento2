<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Admin_Analytics\View_Model;

use Magento\Admin_Analytics\Model\Condition\Can_View_Notification as AdminAnalyticsNotification;
use Magento\Framework\View\Element\Block\Argument_Interface;
use Magento\Release_Notification\Model\Condition\Can_View_Notification as ReleaseNotification;
/**
 * Control display of admin analytics and release notification modals
 */
class Notification implements Argument_Interface
{
    public function __construct(private readonly Admin_Analytics_Notification $can_view_notification_analytics, private readonly Release_Notification $can_view_notification_release)
    {
    }
    /**
     * Determine if the analytics popup is visible
     */
    public function is_analytics_visible(): bool
    {
        return $this->can_view_notification_analytics->is_visible([]);
    }
    /**
     * Determine if the release popup is visible
     */
    public function is_release_visible(): bool
    {
        return $this->can_view_notification_release->is_visible([]);
    }
}