<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\GoogleAnalytics\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * GoogleAnalytics data helper
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Config paths for using throughout the code
     */
    public const XML_PATH_ACTIVE = 'google/analytics/active';

    public const XML_PATH_ACCOUNT = 'google/analytics/account';

    public const XML_PATH_ANONYMIZE = 'google/analytics/anonymize';

    /**
     * Whether GA is ready to use
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isGoogleAnalyticsAvailable($store = null)
    {
        $accountId = $this->scopeConfig->getValue(self::XML_PATH_ACCOUNT, ScopeInterface::SCOPE_STORE, $store);
        return $accountId && $this->scopeConfig->isSetFlag(self::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Whether anonymized IPs are active
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     * @since 100.2.0
     */
    public function isAnonymizedIpActive($store = null)
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ANONYMIZE, ScopeInterface::SCOPE_STORE, $store);
    }
}
