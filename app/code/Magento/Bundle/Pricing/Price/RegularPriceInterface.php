<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

/**
 * Regular price interface
 * @api
 * @since 100.0.2
 */
interface Regular_Price_Interface extends \Magento\Framework\Pricing\Price\Base_Price_Provider_Interface
{
    /**
     * Get Minimal Price Amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_minimal_price();
    /**
     * Get Maximal Price Amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_maximal_price();
}