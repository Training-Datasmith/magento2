<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Model\Condition;

use Magento\AdminAnalytics\Model\ResourceModel\Viewer\Logger;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;

/**
 * Dynamic validator for UI admin analytics notification, control UI component visibility.
 */
class CanViewNotification implements VisibilityConditionInterface
{
    /**
     * Unique condition name.
     */
    private static string $conditionName = 'can_view_admin_usage_notification';

    /**
     * Prefix for cache
     */
    private static string $cachePrefix = 'admin-usage-notification-popup';

    public function __construct(private readonly Logger $viewerLogger, private readonly CacheInterface $cacheStorage)
    {
    }

    /**
     * Validate if notification popup can be shown and set the notification flag
     *
     * @param array $arguments Attributes from element node.
     * @inheritdoc
     */
    public function isVisible(array $arguments): bool
    {
        $cacheKey = self::$cachePrefix;
        $value = $this->cacheStorage->load($cacheKey);
        if ($value !== 'log-exists') {
            $logExists = $this->viewerLogger->checkLogExists();
            if ($logExists) {
                $this->cacheStorage->save('log-exists', $cacheKey);
            }
            return !$logExists;
        }
        return false;
    }

    /**
     * Get condition name
     */
    public function getName(): string
    {
        return self::$conditionName;
    }
}
