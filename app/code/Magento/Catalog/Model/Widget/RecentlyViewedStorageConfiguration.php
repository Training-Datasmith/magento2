<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Widget;

use Magento\Catalog\Model\FrontendStorageConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Configurate all storages that needed for recently viewed widgets
 */
class RecentlyViewedStorageConfiguration implements FrontendStorageConfigurationInterface
{
    /** Recently Viewed lifetime */
    public const XML_LIFETIME_PATH = 'catalog/recently_products/recently_viewed_lifetime';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * RecentlyViewedStorageConfiguration constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Parse lifetime of recently viewed products in widget
     *
     * @inheritdoc
     */
    public function get()
    {
        return [
            'lifetime' => $this->scopeConfig->getValue(self::XML_LIFETIME_PATH),
        ];
    }
}
