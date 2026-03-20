<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver\Product\Price;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Price\Final_Price;
use Magento\Catalog\Pricing\Price\Base_Price;
use Magento\Catalog\Pricing\Price\Regular_Price;
use Magento\Catalog_Graph_Ql\Model\Resolver\Product\Price\Provider_Interface;
use Magento\Framework\Pricing\Amount\Amount_Interface;
use Magento\Framework\Pricing\Saleable_Interface;
/**
 * Provides pricing information for Bundle products
 */
class Provider implements Provider_Interface
{
    /**
     * @inheritdoc
     */
    public function get_minimal_final_price(Saleable_Interface $product): Amount_Interface
    {
        return $product->get_price_info()->get_price(Final_Price::PRICE_CODE)->get_minimal_price();
    }
    /**
     * @inheritdoc
     */
    public function get_minimal_regular_price(Saleable_Interface $product): Amount_Interface
    {
        return $product->get_price_info()->get_price(Regular_Price::PRICE_CODE)->get_minimal_price();
    }
    /**
     * @inheritdoc
     */
    public function get_maximal_final_price(Saleable_Interface $product): Amount_Interface
    {
        return $product->get_price_info()->get_price(Final_Price::PRICE_CODE)->get_maximal_price();
    }
    /**
     * @inheritdoc
     */
    public function get_maximal_regular_price(Saleable_Interface $product): Amount_Interface
    {
        return $product->get_price_info()->get_price(Regular_Price::PRICE_CODE)->get_maximal_price();
    }
    /**
     * @inheritdoc
     */
    public function get_regular_price(Saleable_Interface $product): Amount_Interface
    {
        if ($product->get_price_type() == Price::PRICE_TYPE_FIXED) {
            return $product->get_price_info()->get_price(Base_Price::PRICE_CODE)->get_amount();
        }
        return $product->get_price_info()->get_price(Regular_Price::PRICE_CODE)->get_amount();
    }
}