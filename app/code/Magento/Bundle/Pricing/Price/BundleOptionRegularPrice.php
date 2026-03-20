<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Pricing\Adjustment\Bundle_Calculator_Interface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\Abstract_Price;
use Magento\Framework\Pricing\Price_Currency_Interface;
/**
 * Bundle option price model with final price.
 */
class Bundle_Option_Regular_Price extends Abstract_Price implements Bundle_Option_Price_Interface
{
    /**
     * Price model code.
     */
    public const PRICE_CODE = 'bundle_option_regular_price';
    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;
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
     * {@inheritdoc}
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
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function get_options(): \Magento\Bundle\Model\Resource_Model\Option\Collection
    {
        return $this->bundle_options->get_options($this->product);
    }
    /**
     * Get selection amount.
     *
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_option_selection_amount($selection): \Magento\Framework\Pricing\Amount\Amount_Interface
    {
        return $this->bundle_options->get_option_selection_amount($this->product, $selection, true);
    }
    /**
     * Get minimal amount of bundle price with options.
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_amount(): \Magento\Framework\Pricing\Amount\Amount_Interface
    {
        return $this->calculator->get_options_amount($this->product);
    }
}