<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Adjustment\Bundle_Calculator_Interface;
use Magento\Catalog\Pricing\Price\Custom_Option_Price;
use Magento\Catalog\Pricing\Price\Regular_Price;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Pricing\Amount\Amount_Interface;
/**
 * Bundle product regular price model
 */
class Bundle_Regular_Price extends Regular_Price implements Regular_Price_Interface, Reset_After_Request_Interface
{
    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;
    /**
     * @var AmountInterface
     */
    protected $maximal_price;
    /**
     * @inheritdoc
     */
    public function get_amount()
    {
        $price = $this->get_value();
        $value_index = (string) $price;
        if (!isset($this->amount[$value_index])) {
            if ($this->product->get_price_type() == Price::PRICE_TYPE_FIXED) {
                /** @var \Magento\Catalog\Pricing\Price\CustomOptionPrice $customOptionPrice */
                $custom_option_price = $this->price_info->get_price(Custom_Option_Price::PRICE_CODE);
                $price += $custom_option_price->get_custom_option_range(true, $this->get_price_code());
            }
            $this->amount[$value_index] = $this->calculator->get_min_regular_amount($price, $this->product);
        }
        return $this->amount[$value_index];
    }
    /**
     * Returns max price
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_maximal_price()
    {
        if (null === $this->maximal_price) {
            $price = $this->get_value();
            if ($this->product->get_price_type() == Price::PRICE_TYPE_FIXED) {
                /** @var \Magento\Catalog\Pricing\Price\CustomOptionPrice $customOptionPrice */
                $custom_option_price = $this->price_info->get_price(Custom_Option_Price::PRICE_CODE);
                $price += $custom_option_price->get_custom_option_range(false, $this->get_price_code());
            }
            $this->maximal_price = $this->calculator->get_max_regular_amount($price, $this->product);
        }
        return $this->maximal_price;
    }
    /**
     * Returns min price
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_minimal_price()
    {
        return $this->get_amount();
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->maximal_price = null;
        $this->amount = [];
    }
}