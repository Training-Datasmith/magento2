<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

/**
 * Option price interface
 * @api
 * @since 100.0.2
 */
interface Bundle_Option_Price_Interface
{
    /**
     * Return calculated options
     *
     * @return array
     */
    public function get_options();
    /**
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_option_selection_amount($selection);
}