<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price\Regular_Price;
/**
 * Special price model
 */
class Special_Price extends \Magento\Catalog\Pricing\Price\Special_Price implements Discount_Provider_Interface
{
    /**
     * @var float|false
     */
    protected $percent;
    /**
     * Returns discount percent
     *
     * @return bool|float
     */
    public function get_discount_percent()
    {
        if ($this->percent === null) {
            $this->percent = parent::get_value();
        }
        return $this->percent;
    }
    /**
     * Returns price value
     *
     * @return bool|float
     */
    public function get_value()
    {
        if ($this->value !== null) {
            return $this->value;
        }
        $special_price = $this->get_discount_percent();
        if ($special_price !== false) {
            $regular_price = $this->get_regular_price();
            $this->value = $regular_price * ($special_price / 100);
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
     * Returns true as special price is always percentage for bundle products
     *
     * @return bool
     */
    public function is_percentage_discount()
    {
        return true;
    }
}