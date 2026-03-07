<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\ViewModel;

use Magento\AdminAnalytics\Model\Condition\CanViewNotification as AdminAnalyticsNotification;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\ReleaseNotification\Model\Condition\CanViewNotification as ReleaseNotification;

/**
 * Control display of admin analytics and release notification modals
 */
class Notification implements ArgumentInterface
{
    public function __construct(private readonly AdminAnalyticsNotification $canViewNotificationAnalytics, private readonly ReleaseNotification $canViewNotificationRelease)
    {
    }

    /**
     * Determine if the analytics popup is visible
     */
    public function isAnalyticsVisible(): bool
    {
        return $this->canViewNotificationAnalytics->isVisible([]);
    }

    /**
     * Determine if the release popup is visible
     */
    public function isReleaseVisible(): bool
    {
        return $this->canViewNotificationRelease->isVisible([]);
    }
}
