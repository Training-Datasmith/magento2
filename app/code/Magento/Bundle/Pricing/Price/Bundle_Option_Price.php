<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Model\Resource_Model\Option\Collection;
use Magento\Bundle\Model\Selection;
use Magento\Bundle\Pricing\Adjustment\Bundle_Calculator_Interface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Amount\Amount_Interface;
use Magento\Framework\Pricing\Price\Abstract_Price;
use Magento\Framework\Pricing\Price_Currency_Interface;
/**
 * Bundle option price model with final price.
 */
class Bundle_Option_Price extends Abstract_Price implements Bundle_Option_Price_Interface
{
    /**
     * Price model code
     */
    public const PRICE_CODE = 'bundle_option';
    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;
    /**
     * @var float|bool|null
     */
    protected $maximal_price;
    /**
     * @var BundleOptions
     */
    private $bundle_options;
    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param BundleOptions $bundleOptions
     */
    public function __construct(Product $saleable_item, $quantity, Bundle_Calculator_Interface $calculator, Price_Currency_Interface $price_currency, Bundle_Options $bundle_options)
    {
        parent::__construct($saleable_item, $quantity, $calculator, $price_currency);
        $this->product->set_qty($this->quantity);
        $this->bundle_options = $bundle_options;
    }
    /**
     * @inheritDoc
     */
    public function get_value()
    {
        if (null === $this->value) {
            $this->value = $this->bundle_options->calculate_options($this->product);
        }
        return $this->value;
    }
    /**
     * Get Options with attached Selections collection.
     *
     * @return Collection
     */
    public function get_options()
    {
        return $this->bundle_options->get_options($this->product);
    }
    /**
     * Get selection amount.
     *
     * @param Selection $selection
     *
     * @return AmountInterface
     */
    public function get_option_selection_amount($selection)
    {
        return $this->bundle_options->get_option_selection_amount($this->product, $selection, false);
    }
    /**
     * Calculate maximal or minimal options value.
     *
     * @param bool $searchMin
     *
     * @return bool|float
     */
    protected function calculate_options($search_min = true)
    {
        return $this->bundle_options->calculate_options($this->product, $search_min);
    }
    /**
     * Get minimal amount of bundle price with options
     *
     * @return AmountInterface
     */
    public function get_amount()
    {
        return $this->calculator->get_options_amount($this->product);
    }
}