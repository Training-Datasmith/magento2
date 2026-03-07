<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\System\Message;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

/**
 * Represents an analytics notification about failed subscription.
 */
class NotificationAboutFailedSubscription implements MessageInterface
{
    public function __construct(private readonly SubscriptionStatusProvider $subscriptionStatusProvider, private readonly UrlInterface $urlBuilder)
    {
    }

    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function getIdentity(): string
    {
        return hash('sha256', 'ANALYTICS_NOTIFICATION');
    }

    /**
     * @inheritdoc
     */
    public function isDisplayed(): bool
    {
        return $this->subscriptionStatusProvider->getStatus() === SubscriptionStatusProvider::FAILED;
    }

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        $messageDetails = '';

        $messageDetails .= __('Failed to synchronize data to the Magento Business Intelligence service. ');

        return $messageDetails . ('<a href="' . $this->urlBuilder->getUrl('analytics/subscription/retry') . '">' . __('Retry Synchronization') . '</a>');
    }

    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function getSeverity(): int
    {
        return self::SEVERITY_MAJOR;
    }
}
