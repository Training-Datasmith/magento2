<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

/**
 * Interface FinalPriceInterface
 * @api
 * @since 100.0.2
 */
interface Final_Price_Interface extends \Magento\Catalog\Pricing\Price\Final_Price_Interface
{
    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_price_without_option();
}