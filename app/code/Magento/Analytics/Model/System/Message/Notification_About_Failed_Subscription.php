<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\System\Message;

use Magento\Analytics\Model\Subscription_Status_Provider;
use Magento\Framework\Notification\Message_Interface;
use Magento\Framework\Url_Interface;
/**
 * Represents an analytics notification about failed subscription.
 */
class Notification_About_Failed_Subscription implements Message_Interface
{
    public function __construct(private readonly Subscription_Status_Provider $subscription_status_provider, private readonly Url_Interface $url_builder)
    {
    }
    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function get_identity(): string
    {
        return hash('sha256', 'ANALYTICS_NOTIFICATION');
    }
    /**
     * @inheritdoc
     */
    public function is_displayed(): bool
    {
        return $this->subscription_status_provider->get_status() === Subscription_Status_Provider::FAILED;
    }
    /**
     * @inheritdoc
     */
    public function get_text(): string
    {
        $message_details = '';
        $message_details .= __('Failed to synchronize data to the Magento Business Intelligence service. ');
        return $message_details . ('<a href="' . $this->url_builder->get_url('analytics/subscription/retry') . '">' . __('Retry Synchronization') . '</a>');
    }
    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function get_severity(): int
    {
        return self::SEVERITY_MAJOR;
    }
}