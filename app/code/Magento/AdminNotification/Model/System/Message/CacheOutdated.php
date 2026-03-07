<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Model\System\Message;

/**
 * @api
 * @since 100.0.2
 */
class CacheOutdated implements \Magento\Framework\Notification\MessageInterface
{
    public function __construct(protected \Magento\Framework\AuthorizationInterface $_authorization, protected \Magento\Framework\UrlInterface $_urlBuilder, protected \Magento\Framework\App\Cache\TypeListInterface $_cacheTypeList)
    {
    }

    /**
     * Get array of cache types which require data refresh
     */
    protected function _getCacheTypesForRefresh(): array
    {
        $output = [];
        foreach ($this->_cacheTypeList->getInvalidated() as $type) {
            $output[] = $type->getCacheType();
        }
        return $output;
    }

    /**
     * Retrieve unique message identity
     */
    public function getIdentity(): string
    {
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        return md5('cache' . implode(':', $this->_getCacheTypesForRefresh()));
    }

    /**
     * Check whether
     */
    public function isDisplayed(): bool
    {
        return $this->_authorization->isAllowed(
            'Magento_Backend::cache'
        ) && count(
            $this->_getCacheTypesForRefresh()
        ) > 0;
    }

    /**
     * Retrieve message text
     */
    public function getText(): string
    {
        $cacheTypes = implode(', ', $this->_getCacheTypesForRefresh());
        $message = __('One or more of the Cache Types are invalidated: %1. ', $cacheTypes) . ' ';
        $url = $this->_urlBuilder->getUrl('adminhtml/cache');
        return $message . __('Please go to <a href="%1">Cache Management</a> and refresh cache types.', $url);
    }

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->_urlBuilder->getUrl('adminhtml/cache');
    }

    /**
     * Retrieve message severity
     */
    public function getSeverity(): int
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
    }
}
