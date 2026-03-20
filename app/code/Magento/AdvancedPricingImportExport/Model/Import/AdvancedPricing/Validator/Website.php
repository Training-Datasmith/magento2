<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Advanced_Pricing_Import_Export\Model\Import\Advanced_Pricing\Validator;

use Magento\Advanced_Pricing_Import_Export\Model\Currency_Resolver;
use Magento\Advanced_Pricing_Import_Export\Model\Import\Advanced_Pricing;
use Magento\Catalog_Import_Export\Model\Import\Product\Row_Validator_Interface;
use Magento\Catalog_Import_Export\Model\Import\Product\Validator\Abstract_Import_Validator;
use Magento\Framework\App\Object_Manager;
class Website extends Abstract_Import_Validator implements Row_Validator_Interface
{
    /**
     * @var CurrencyResolver
     */
    private $currency_resolver;
    public function __construct(protected \Magento\Catalog_Import_Export\Model\Import\Product\Store_Resolver $store_resolver, protected \Magento\Store\Model\Website $website_model, ?Currency_Resolver $currency_resolver = null)
    {
        $this->currency_resolver = $currency_resolver ?? Object_Manager::get_instance()->get(Currency_Resolver::class);
    }
    /**
     * Validate by website type
     *
     * @param string $websiteCode
     *
     */
    protected function is_website_valid(array $value, $website_code): bool
    {
        if (!isset($value[$website_code])) {
            return true;
        }
        if (!!empty($value[$website_code])) {
            return true;
        }
        if ($value[$website_code] != $this->get_all_websites_value() && !$this->store_resolver->get_website_code_to_id($value[$website_code])) {
            return false;
        }
        return true;
    }
    /**
     * Validate value
     *
     *
     */
    public function is_valid(array $value): float|int|true
    {
        $this->_clear_messages();
        $valid = true;
        if (isset($value[Advanced_Pricing::COL_TIER_PRICE]) && !empty($value[Advanced_Pricing::COL_TIER_PRICE])) {
            $valid *= $this->is_website_valid($value, Advanced_Pricing::COL_TIER_PRICE_WEBSITE);
        }
        if (!$valid) {
            $this->_add_messages([self::ERROR_INVALID_WEBSITE]);
        }
        return $valid;
    }
    /**
     * Get all websites value with currency code
     */
    public function get_all_websites_value(): string
    {
        return Advanced_Pricing::VALUE_ALL_WEBSITES . ' [' . $this->currency_resolver->get_default_base_currency() . ']';
    }
}