<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Pricing\Price;

/**
 * Configured regular price model.
 */
class Configured_Regular_Price extends Configured_Price
{
    /**
     * Price type configured.
     */
    public const PRICE_CODE = 'configured_regular_price';
    /**
     * Create Selection Price List.
     *
     * @param \Magento\Bundle\Model\Option $option
     * @return BundleSelectionPrice[]
     */
    protected function create_selection_price_list(\Magento\Bundle\Model\Option $option): array
    {
        return $this->calculator->create_selection_price_list($option, $this->product, true);
    }
}