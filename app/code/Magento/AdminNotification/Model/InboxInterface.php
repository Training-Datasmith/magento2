<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model;

/**
 * AdminNotification Inbox interface
 *
 * @api
 * @since 100.0.2
 */
interface Inbox_Interface
{
    /**
     * Retrieve Severity collection array
     *
     * @param int|null $severity
     * @return array|string|null
     */
    public function get_severities($severity = null);
    /**
     * Retrieve Latest Notice
     *
     * @return $this
     */
    public function load_latest_notice();
    /**
     * Retrieve notice statuses
     *
     * @return array
     */
    public function get_notice_status();
}