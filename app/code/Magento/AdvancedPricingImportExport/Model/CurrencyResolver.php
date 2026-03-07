<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Model;

use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Currency resolver for tier price scope
 */
class CurrencyResolver
{
    /**
     * @var string
     */
    private $defaultBaseCurrency;

    /**
     * Associative array with website code as the key and base currency as the value
     */
    private ?array $websitesBaseCurrency = null;

    public function __construct(private readonly StoreManagerInterface $storeManager, private readonly DirectoryData $directoryData)
    {
    }

    /**
     * Get base currency for all websites
     *
     * @return array associative array with website code as the key and base currency as the value
     */
    public function getWebsitesBaseCurrency(): array
    {
        if ($this->websitesBaseCurrency === null) {
            $this->websitesBaseCurrency = [];
            foreach ($this->storeManager->getWebsites() as $website) {
                $this->websitesBaseCurrency[$website->getCode()] = $website->getBaseCurrencyCode();
            }
        }

        return $this->websitesBaseCurrency;
    }

    /**
     * Get default scope base currency
     */
    public function getDefaultBaseCurrency(): string
    {
        if ($this->defaultBaseCurrency === null) {
            $this->defaultBaseCurrency = $this->directoryData->getBaseCurrencyCode();
        }

        return $this->defaultBaseCurrency;
    }
}
