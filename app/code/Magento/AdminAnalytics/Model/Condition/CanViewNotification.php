<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Admin_Analytics\Model\Condition;

use Magento\Admin_Analytics\Model\Resource_Model\Viewer\Logger;
use Magento\Framework\App\Cache_Interface;
use Magento\Framework\View\Layout\Condition\Visibility_Condition_Interface;
/**
 * Dynamic validator for UI admin analytics notification, control UI component visibility.
 */
class Can_View_Notification implements Visibility_Condition_Interface
{
    /**
     * Unique condition name.
     */
    private static string $condition_name = 'can_view_admin_usage_notification';
    /**
     * Prefix for cache
     */
    private static string $cache_prefix = 'admin-usage-notification-popup';
    public function __construct(private readonly Logger $viewer_logger, private readonly Cache_Interface $cache_storage)
    {
    }
    /**
     * Validate if notification popup can be shown and set the notification flag
     *
     * @param array $arguments Attributes from element node.
     * @inheritdoc
     */
    public function is_visible(array $arguments): bool
    {
        $cache_key = self::$cache_prefix;
        $value = $this->cache_storage->load($cache_key);
        if ($value !== 'log-exists') {
            $log_exists = $this->viewer_logger->check_log_exists();
            if ($log_exists) {
                $this->cache_storage->save('log-exists', $cache_key);
            }
            return !$log_exists;
        }
        return false;
    }
    /**
     * Get condition name
     */
    public function get_name(): string
    {
        return self::$condition_name;
    }
}