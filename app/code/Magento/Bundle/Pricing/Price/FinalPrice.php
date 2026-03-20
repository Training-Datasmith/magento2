<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\Product_Custom_Option_Repository_Interface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\Custom_Option_Price;
use Magento\Framework\Pricing\Adjustment\Calculator_Interface;
use Magento\Framework\Pricing\Amount\Amount_Interface;
use Magento\Framework\Pricing\Price_Currency_Interface;
/**
 * Final price model
 */
class Final_Price extends \Magento\Catalog\Pricing\Price\Final_Price implements Final_Price_Interface
{
    /**
     * @var AmountInterface
     */
    protected $maximal_price;
    /**
     * @var AmountInterface
     */
    protected $minimal_price;
    /**
     * @var AmountInterface
     */
    protected $price_without_option;
    /**
     * @var BundleOptionPrice
     */
    protected $bundle_option_price;
    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $product_option_repository;
    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductCustomOptionRepositoryInterface $productOptionRepository
     */
    public function __construct(Product $saleable_item, $quantity, Calculator_Interface $calculator, Price_Currency_Interface $price_currency, Product_Custom_Option_Repository_Interface $product_option_repository)
    {
        parent::__construct($saleable_item, $quantity, $calculator, $price_currency);
        $this->product_option_repository = $product_option_repository;
    }
    /**
     * Returns price value
     *
     * @return float
     */
    public function get_value()
    {
        return parent::get_value() + $this->get_bundle_option_price()->get_value();
    }
    /**
     * Returns max price
     *
     * @return AmountInterface
     */
    public function get_maximal_price()
    {
        if (!$this->maximal_price) {
            $price = $this->get_base_price()->get_value();
            if ($this->product->get_price_type() == Price::PRICE_TYPE_FIXED) {
                /** @var CustomOptionPrice $customOptionPrice */
                $custom_option_price = $this->price_info->get_price(Custom_Option_Price::PRICE_CODE);
                $price += $custom_option_price->get_custom_option_range(false);
            }
            $this->maximal_price = $this->calculator->get_max_amount($price, $this->product);
        }
        return $this->maximal_price;
    }
    /**
     * Returns min price
     *
     * @return AmountInterface
     */
    public function get_minimal_price()
    {
        return $this->get_amount();
    }
    /**
     * Returns price amount
     *
     * @return AmountInterface
     */
    public function get_amount()
    {
        if (!$this->minimal_price) {
            $price = parent::get_value();
            if ($this->product->get_price_type() == Price::PRICE_TYPE_FIXED) {
                $this->load_product_custom_options();
                /** @var CustomOptionPrice $customOptionPrice */
                $custom_option_price = $this->price_info->get_price(Custom_Option_Price::PRICE_CODE);
                $price += $custom_option_price->get_custom_option_range(true);
            }
            $this->minimal_price = $this->calculator->get_amount($price, $this->product);
        }
        return $this->minimal_price;
    }
    /**
     * Load product custom options
     *
     * @return void
     */
    private function load_product_custom_options()
    {
        if (!$this->product->get_options()) {
            $options = [];
            foreach ($this->product_option_repository->get_product_options($this->product) as $option) {
                $option->set_product($this->product);
                $options[] = $option;
            }
            $this->product->set_options($options);
        }
    }
    /**
     * Get bundle product price without any option
     *
     * @return AmountInterface
     */
    public function get_price_without_option()
    {
        if (!$this->price_without_option) {
            $this->price_without_option = $this->calculator->get_amount_without_option(parent::get_value(), $this->product);
        }
        return $this->price_without_option;
    }
    /**
     * Returns option price
     *
     * @return BundleOptionPrice
     */
    protected function get_bundle_option_price()
    {
        if (!$this->bundle_option_price) {
            $this->bundle_option_price = $this->price_info->get_price(Bundle_Option_Price::PRICE_CODE);
        }
        return $this->bundle_option_price;
    }
}