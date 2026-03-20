<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Pricing\Price as CatalogPrice;
use Magento\Framework\Event\Manager_Interface;
use Magento\Framework\Pricing\Adjustment\Calculator_Interface;
use Magento\Framework\Pricing\Amount\Amount_Interface;
use Magento\Framework\Pricing\Price\Abstract_Price;
use Magento\Framework\Pricing\Saleable_Interface;
/**
 * Bundle option price
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Bundle_Selection_Price extends Abstract_Price
{
    /**
     * Price model code
     */
    public const PRICE_CODE = 'bundle_selection';
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $bundle_product;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $event_manager;
    /**
     * @var DiscountCalculator
     */
    protected $discount_calculator;
    /**
     * @var bool
     */
    protected $use_regular_price;
    /**
     * @var Product
     */
    protected $selection;
    /**
     * Code of parent adjustment to be skipped from calculation
     *
     * @var string
     */
    protected $exclude_adjustment = null;
    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param SaleableInterface $bundleProduct
     * @param ManagerInterface $eventManager
     * @param DiscountCalculator $discountCalculator
     * @param bool $useRegularPrice
     * @param array $excludeAdjustment
     */
    public function __construct(Product $saleable_item, $quantity, Calculator_Interface $calculator, \Magento\Framework\Pricing\Price_Currency_Interface $price_currency, Saleable_Interface $bundle_product, Manager_Interface $event_manager, Discount_Calculator $discount_calculator, $use_regular_price = false, $exclude_adjustment = null)
    {
        parent::__construct($saleable_item, $quantity, $calculator, $price_currency);
        $this->bundle_product = $bundle_product;
        $this->event_manager = $event_manager;
        $this->discount_calculator = $discount_calculator;
        $this->use_regular_price = $use_regular_price;
        $this->selection = $saleable_item;
        $this->exclude_adjustment = $exclude_adjustment;
    }
    /**
     * Get the price value for one of selection product.
     *
     * @return bool|float
     */
    public function get_value()
    {
        if (null !== $this->value) {
            return $this->value;
        }
        $product = $this->selection;
        $bundle_selection_key = 'bundle-selection-' . ($this->use_regular_price ? 'regular-' : '') . 'value-' . $product->get_selection_id();
        if ($product->has_data($bundle_selection_key)) {
            return $product->get_data($bundle_selection_key);
        }
        $price_code = $this->use_regular_price ? Bundle_Regular_Price::PRICE_CODE : Final_Price::PRICE_CODE;
        if ($this->bundle_product->get_price_type() == Price::PRICE_TYPE_DYNAMIC) {
            // just return whatever the product's value is
            $value = $this->price_info->get_price($price_code)->get_value();
        } else {
            // don't multiply by quantity.  Instead just keep as quantity = 1
            $selection_price_value = $this->selection->get_selection_price_value();
            if ($this->product->get_selection_price_type()) {
                // calculate price for selection type percent
                $price = $this->bundle_product->get_price_info()->get_price(Catalog_Price\Regular_Price::PRICE_CODE)->get_value();
                $product = clone $this->bundle_product;
                $product->set_final_price($price);
                $this->event_manager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $this->bundle_product->get_qty()]);
                $price = $this->use_regular_price ? $product->get_data('price') : $product->get_data('final_price');
                $value = $price * ($selection_price_value / 100);
            } else {
                // calculate price for selection type fixed
                $value = $this->price_currency->convert($selection_price_value);
            }
        }
        if (!$this->use_regular_price) {
            $value = $this->discount_calculator->calculate_discount($this->bundle_product, $value);
        }
        $this->value = $this->price_currency->round_price($value, 4);
        $product->set_data($bundle_selection_key, $this->value);
        return $this->value;
    }
    /**
     * Get Price Amount object
     *
     * @return AmountInterface
     */
    public function get_amount()
    {
        $product = $this->selection;
        $bundle_selection_key = 'bundle-selection' . ($this->use_regular_price ? 'regular-' : '') . '-amount-' . $product->get_selection_id();
        if ($product->has_data($bundle_selection_key)) {
            return $product->get_data($bundle_selection_key);
        }
        $value = (string) $this->get_value();
        if (!isset($this->amount[$value])) {
            $exclude = null;
            if ($this->get_product()->get_type_id() === Type::TYPE_BUNDLE) {
                $exclude = $this->exclude_adjustment;
            }
            $this->amount[$value] = $this->calculator->get_amount($value, $this->get_product(), $exclude);
            $product->set_data($bundle_selection_key, $this->amount[$value]);
        }
        return $this->amount[$value];
    }
    /**
     * Returns the bundle product.
     *
     * @return SaleableInterface
     */
    public function get_product()
    {
        if ($this->bundle_product->get_price_type() == Price::PRICE_TYPE_DYNAMIC) {
            return parent::get_product();
        }
        return $this->bundle_product;
    }
}