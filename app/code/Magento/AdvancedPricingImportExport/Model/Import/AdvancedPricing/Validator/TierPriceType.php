<?php

/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Advanced_Pricing_Import_Export\Model\Import\Advanced_Pricing\Validator;

use Magento\Advanced_Pricing_Import_Export\Model\Import\Advanced_Pricing;
use Magento\Catalog_Import_Export\Model\Import\Product\Row_Validator_Interface;
use Magento\Catalog_Import_Export\Model\Import\Product\Validator\Abstract_Import_Validator;
/**
 * Class TierPriceType validates tier price type.
 */
class Tier_Price_Type extends Abstract_Import_Validator
{
    /**
     * Validate tier price type.
     *
     *
     * @return bool
     */
    public function is_valid(array $value)
    {
        $is_valid = true;
        if (isset($value[Advanced_Pricing::COL_TIER_PRICE_TYPE]) && !empty($value[Advanced_Pricing::COL_TIER_PRICE_TYPE]) && !in_array($value[Advanced_Pricing::COL_TIER_PRICE_TYPE], [Advanced_Pricing::TIER_PRICE_TYPE_FIXED, Advanced_Pricing::TIER_PRICE_TYPE_PERCENT])) {
            $this->_add_messages([Row_Validator_Interface::ERROR_INVALID_TIER_PRICE_TYPE]);
            $is_valid = false;
        }
        return $is_valid;
    }
}