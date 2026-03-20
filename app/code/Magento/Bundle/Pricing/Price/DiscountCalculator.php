<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price_Currency_Interface;
/**
 * Check the product available discount and apply the correct discount to the price
 */
class Discount_Calculator
{
    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(private readonly Price_Currency_Interface $price_currency)
    {
    }
    /**
     * Apply percentage discount
     *
     * @param Product $product
     * @param float|null $value
     * @return float|null
     */
    public function calculate_discount(Product $product, $value = null)
    {
        if ($value === null) {
            $value = $product->get_price_info()->get_price(Final_Price::PRICE_CODE)->get_value();
        }
        $discount = null;
        foreach ($product->get_price_info()->get_prices() as $price) {
            if ($price instanceof Discount_Provider_Interface && $price->get_discount_percent()) {
                $discount = min($price->get_discount_percent(), $discount ?: $price->get_discount_percent());
            }
        }
        return null !== $discount ? $this->price_currency->round_price($discount / 100 * $value, 2) : $value;
    }
}