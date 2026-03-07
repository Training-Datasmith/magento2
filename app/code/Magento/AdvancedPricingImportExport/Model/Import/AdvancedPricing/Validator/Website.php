<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\CurrencyResolver;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator;
use Magento\Framework\App\ObjectManager;

class Website extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var CurrencyResolver
     */
    private $currencyResolver;

    public function __construct(
        protected \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        protected \Magento\Store\Model\Website $websiteModel,
        ?CurrencyResolver $currencyResolver = null
    ) {
        $this->currencyResolver = $currencyResolver ?? ObjectManager::getInstance()->get(CurrencyResolver::class);
    }

    /**
     * Validate by website type
     *
     * @param string $websiteCode
     *
     */
    protected function isWebsiteValid(array $value, $websiteCode): bool
    {
        if (!isset($value[$websiteCode])) {
            return true;
        }
        if (!!empty($value[$websiteCode])) {
            return true;
        }
        if ($value[$websiteCode] != $this->getAllWebsitesValue()
            && !$this->storeResolver->getWebsiteCodeToId($value[$websiteCode])) {
            return false;
        }
        return true;
    }

    /**
     * Validate value
     *
     *
     */
    public function isValid(array $value): float|int|true
    {
        $this->_clearMessages();
        $valid = true;
        if (isset($value[AdvancedPricing::COL_TIER_PRICE]) && !empty($value[AdvancedPricing::COL_TIER_PRICE])) {
            $valid *= $this->isWebsiteValid($value, AdvancedPricing::COL_TIER_PRICE_WEBSITE);
        }
        if (!$valid) {
            $this->_addMessages([self::ERROR_INVALID_WEBSITE]);
        }
        return $valid;
    }

    /**
     * Get all websites value with currency code
     */
    public function getAllWebsitesValue(): string
    {
        return AdvancedPricing::VALUE_ALL_WEBSITES .
            ' [' . $this->currencyResolver->getDefaultBaseCurrency() . ']';
    }
}
