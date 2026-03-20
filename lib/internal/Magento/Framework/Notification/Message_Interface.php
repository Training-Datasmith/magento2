<?php

declare(strict_types=1);
/**
 * System message
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Notification;

/**
 * Interface for system messages
 *
 * Interface MessageInterface
 *
 * @api
 * @since 100.0.2
 */
interface MessageInterface
{
    public const SEVERITY_CRITICAL = 1;

    public const SEVERITY_MAJOR = 2;

    public const SEVERITY_MINOR = 3;

    public const SEVERITY_NOTICE = 4;

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity();

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed();

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText();

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity();
}
