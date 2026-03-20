<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price\Regular_Price;
use Magento\Framework\Pricing\Price_Info_Interface;
/**
 * Bundle tier prices model
 */
class Tier_Price extends \Magento\Catalog\Pricing\Price\Tier_Price implements Discount_Provider_Interface
{
    /**
     * @var bool
     */
    protected $filter_by_base_price = false;
    /**
     * @var float|false
     */
    protected $percent;
    /**
     * Returns percent discount
     *
     * @return bool|float
     */
    public function get_discount_percent()
    {
        if ($this->percent === null) {
            $prices = $this->get_stored_tier_prices();
            $prev_qty = Price_Info_Interface::PRODUCT_QUANTITY_DEFAULT;
            $this->value = $prev_price = false;
            $price_group = $this->group_management->get_all_customers_group()->get_id();
            foreach ($prices as $price) {
                if (!$this->can_apply_tier_price($price, $price_group, $prev_qty) || !isset($price['percentage_value']) || !is_numeric($price['percentage_value'])) {
                    continue;
                }
                if (false === $prev_price || $this->is_first_price_better($price['website_price'], $prev_price)) {
                    $prev_price = $price['website_price'];
                    $prev_qty = $price['price_qty'];
                    $price_group = $price['cust_group'];
                    $this->percent = max(0, min(100, 100 - $price['percentage_value']));
                }
            }
        }
        return $this->percent;
    }
    /**
     * Returns pricing value
     *
     * @return bool|float
     */
    public function get_value()
    {
        if ($this->value !== null) {
            return $this->value;
        }
        $tier_price = $this->get_discount_percent();
        if ($tier_price) {
            $regular_price = $this->get_regular_price();
            $this->value = $regular_price * ($tier_price / 100);
        } else {
            $this->value = false;
        }
        return $this->value;
    }
    /**
     * Returns regular price
     *
     * @return bool|float
     */
    protected function get_regular_price()
    {
        return $this->price_info->get_price(Regular_Price::PRICE_CODE)->get_value();
    }
    /**
     * Returns true if first price is better
     *
     * Method filters tiers price values, higher discount value is better
     *
     * @param float $firstPrice
     * @param float $secondPrice
     * @return bool
     */
    protected function is_first_price_better($first_price, $second_price)
    {
        return $first_price > $second_price;
    }
    /**
     * @return bool
     */
    public function is_percentage_discount()
    {
        return true;
    }
}