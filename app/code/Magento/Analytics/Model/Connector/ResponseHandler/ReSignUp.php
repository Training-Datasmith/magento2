<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;
use Magento\Analytics\Model\SubscriptionStatusProvider;

/**
 * Removes stored token and triggers subscription process.
 */
class ReSignUp implements ResponseHandlerInterface
{
    public function __construct(private readonly AnalyticsToken $analyticsToken, private readonly SubscriptionHandler $subscriptionHandler, private readonly SubscriptionStatusProvider $subscriptionStatusProvider)
    {
    }

    /**
     * @inheritdoc
     */
    public function handleResponse(array $responseBody): bool
    {
        if ($this->subscriptionStatusProvider->getStatus() === SubscriptionStatusProvider::ENABLED) {
            $this->analyticsToken->storeToken(null);
            $this->subscriptionHandler->processEnabled();
        }
        return false;
    }
}
