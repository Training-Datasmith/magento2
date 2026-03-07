<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Plugin;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Store\Model\Store;

/**
 * Plugin on Base URL config value AfterSave method.
 */
class BaseUrlConfigPlugin
{
    public function __construct(private readonly SubscriptionUpdateHandler $subscriptionUpdateHandler)
    {
    }

    /**
     * Add additional handling after config value was saved.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(
        Value $subject,
        Value $result
    ): Value {
        if ($this->isPluginApplicable($result)) {
            $this->subscriptionUpdateHandler->processUrlUpdate($result->getOldValue());
        }

        return $result;
    }

    /**
     * Check is need to apply the plugin logic
     */
    private function isPluginApplicable(Value $result): bool
    {
        return $result->isValueChanged()
            && ($result->getPath() === Store::XML_PATH_SECURE_BASE_URL)
            && ($result->getScope() === ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }
}
