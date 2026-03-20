<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Price\Bundle_Selection_Factory;
use Magento\Bundle\Pricing\Price\Bundle_Selection_Price;
use Magento\Catalog\Model\Product;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Pricing\Adjustment\Calculator as CalculatorBase;
use Magento\Framework\Pricing\Amount\Amount_Factory;
use Magento\Framework\Pricing\Amount\Amount_Interface;
use Magento\Framework\Pricing\Price_Currency_Interface;
use Magento\Framework\Pricing\Saleable_Interface;
use Magento\Tax\Helper\Data as TaxHelper;
/**
 * Bundle price calculator
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Calculator implements Bundle_Calculator_Interface, Reset_After_Request_Interface
{
    /**
     * @var CalculatorBase
     */
    protected $calculator;
    /**
     * @var AmountFactory
     */
    protected $amount_factory;
    /**
     * @var BundleSelectionFactory
     */
    protected $selection_factory;
    /**
     * Tax helper, needed to get rounding setting
     *
     * @var TaxHelper
     */
    protected $tax_helper;
    /**
     * @var PriceCurrencyInterface
     */
    protected $price_currency;
    /**
     * @var AmountInterface[]
     */
    private $option_amount = [];
    /**
     * @var SelectionPriceListProviderInterface
     */
    private $selection_price_list_provider;
    /**
     * @param CalculatorBase $calculator
     * @param AmountFactory $amountFactory
     * @param BundleSelectionFactory $bundleSelectionFactory
     * @param TaxHelper $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param SelectionPriceListProviderInterface $selectionPriceListProvider
     */
    public function __construct(Calculator_Base $calculator, Amount_Factory $amount_factory, Bundle_Selection_Factory $bundle_selection_factory, Tax_Helper $tax_helper, Price_Currency_Interface $price_currency, Selection_Price_List_Provider_Interface $selection_price_list_provider)
    {
        $this->calculator = $calculator;
        $this->amount_factory = $amount_factory;
        $this->selection_factory = $bundle_selection_factory;
        $this->tax_helper = $tax_helper;
        $this->price_currency = $price_currency;
        $this->selection_price_list_provider = $selection_price_list_provider;
    }
    /**
     * Get amount for current product which is included price of existing options with minimal price
     *
     * @param float|string $amount
     * @param SaleableInterface $saleableItem
     * @param null|bool|string|array $exclude
     * @param null|array $context
     *
     * @return AmountInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_amount($amount, Saleable_Interface $saleable_item, $exclude = null, $context = [])
    {
        return $this->get_options_amount($saleable_item, $exclude, true, $amount);
    }
    /**
     * Get amount for current product which is included price of existing options with maximal price
     *
     * @param float $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     *
     * @return AmountInterface
     */
    public function get_min_regular_amount($amount, Product $saleable_item, $exclude = null)
    {
        return $this->get_options_amount($saleable_item, $exclude, true, $amount, true);
    }
    /**
     * Get amount for current product which is included price of existing options with maximal price
     *
     * @param float $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     *
     * @return AmountInterface
     */
    public function get_max_amount($amount, Product $saleable_item, $exclude = null)
    {
        return $this->get_options_amount($saleable_item, $exclude, false, $amount);
    }
    /**
     * Get amount for current product which is included price of existing options with maximal price
     *
     * @param float $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     *
     * @return AmountInterface
     */
    public function get_max_regular_amount($amount, Product $saleable_item, $exclude = null)
    {
        return $this->get_options_amount($saleable_item, $exclude, false, $amount, true);
    }
    /**
     * Option amount calculation for bundle product
     *
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @param bool $searchMin
     * @param float $baseAmount
     * @param bool $useRegularPrice
     *
     * @return AmountInterface
     */
    public function get_options_amount(Product $saleable_item, $exclude = null, $search_min = true, $base_amount = 0.0, $use_regular_price = false)
    {
        $cache_key = implode('-', [$saleable_item->get_id(), $exclude, $search_min, $base_amount, $use_regular_price]);
        if (!isset($this->option_amount[$cache_key])) {
            $this->option_amount[$cache_key] = $this->calculate_bundle_amount($base_amount, $saleable_item, $this->get_selection_amounts($saleable_item, $search_min, $use_regular_price), $exclude);
        }
        return $this->option_amount[$cache_key];
    }
    /**
     * Get base amount without option
     *
     * @param float $amount
     * @param Product $saleableItem
     *
     * @return AmountInterface|void
     */
    public function get_amount_without_option($amount, Product $saleable_item)
    {
        return $this->calculate_bundle_amount($amount, $saleable_item, []);
    }
    /**
     * Filter all options for bundle product
     *
     * @param Product $bundleProduct
     * @param bool $searchMin
     * @param bool $useRegularPrice
     * @return array
     */
    protected function get_selection_amounts(Product $bundle_product, $search_min, $use_regular_price = false)
    {
        return $this->selection_price_list_provider->get_price_list($bundle_product, $search_min, $use_regular_price);
    }
    /**
     * Check this option if it should be skipped
     *
     * @param Option $option
     * @param bool $canSkipRequiredOption
     * @return bool
     * @deprecated 100.2.0 Not used anymore.
     * @see Nothing
     */
    protected function can_skip_option($option, $can_skip_required_option)
    {
        return !$option->get_selections() || $can_skip_required_option && !$option->get_required();
    }
    /**
     * Check the bundle product for availability of required options
     *
     * @param Product $bundleProduct
     * @return bool
     * @deprecated 100.2.0 Not used anymore.
     * @see Nothing
     */
    protected function has_required_option($bundle_product)
    {
        $options = array_filter($this->get_bundle_options($bundle_product), function ($item) {
            return $item->get_required();
        });
        return !empty($options);
    }
    /**
     * Get bundle options
     *
     * @param Product $saleableItem
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     * @deprecated 100.2.0
     * @see Nothing
     */
    protected function get_bundle_options(Product $saleable_item)
    {
        /** @var \Magento\Bundle\Pricing\Price\BundleOptionPrice $bundlePrice */
        $bundle_price = $saleable_item->get_price_info()->get_price(\Magento\Bundle\Pricing\Price\Bundle_Option_Price::PRICE_CODE);
        return $bundle_price->get_options();
    }
    /**
     * Calculate amount for bundle product with all selection prices
     *
     * @param float $basePriceValue
     * @param Product $bundleProduct
     * @param BundleSelectionPrice[] $selectionPriceList
     * @param null|bool|string|array $exclude
     * @return AmountInterface
     */
    public function calculate_bundle_amount($base_price_value, $bundle_product, $selection_price_list, $exclude = null)
    {
        if ($bundle_product->get_price_type() == Price::PRICE_TYPE_FIXED) {
            return $this->calculate_fixed_bundle_amount($base_price_value, $bundle_product, $selection_price_list, $exclude);
        }
        return $this->calculate_dynamic_bundle_amount($base_price_value, $bundle_product, $selection_price_list, $exclude);
    }
    /**
     * Calculate amount for fixed bundle product
     *
     * @param float $basePriceValue
     * @param Product $bundleProduct
     * @param BundleSelectionPrice[] $selectionPriceList
     * @param null|bool|string|array $exclude
     * @return AmountInterface
     */
    protected function calculate_fixed_bundle_amount($base_price_value, $bundle_product, $selection_price_list, $exclude)
    {
        $full_amount = $base_price_value;
        /** @var $option Option */
        foreach ($selection_price_list as $selection_price) {
            $full_amount += $selection_price->get_value() * $selection_price->get_quantity();
        }
        return $this->calculator->get_amount($full_amount, $bundle_product, $exclude);
    }
    /**
     * Calculate amount for dynamic bundle product
     *
     * @param float $basePriceValue
     * @param Product $bundleProduct
     * @param BundleSelectionPrice[] $selectionPriceList
     * @param null|bool|string|array $exclude
     * @return AmountInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function calculate_dynamic_bundle_amount($base_price_value, $bundle_product, $selection_price_list, $exclude)
    {
        $full_amount = 0.0;
        $adjustments = [];
        $i = 0;
        $amount_list[$i]['amount'] = $this->calculator->get_amount($base_price_value, $bundle_product, $exclude);
        $amount_list[$i]['quantity'] = 1;
        foreach ($selection_price_list as $selection_price) {
            ++$i;
            if ($selection_price) {
                $amount_list[$i]['amount'] = $selection_price->get_amount();
                // always honor the quantity given
                $amount_list[$i]['quantity'] = $selection_price->get_quantity();
            }
        }
        foreach ($amount_list as $amount_info) {
            /** @var AmountInterface $itemAmount */
            $item_amount = $amount_info['amount'];
            $qty = $amount_info['quantity'];
            //We need to round the individual selection first
            $full_amount += $this->price_currency->round($item_amount->get_value()) * $qty;
            foreach ($item_amount->get_adjustment_amounts() as $code => $adjustment) {
                $adjustment = $this->price_currency->round($adjustment) * $qty;
                $adjustments[$code] = isset($adjustments[$code]) ? $adjustments[$code] + $adjustment : $adjustment;
            }
        }
        if (is_array($exclude) == false) {
            if ($exclude && isset($adjustments[$exclude])) {
                $full_amount -= $adjustments[$exclude];
                unset($adjustments[$exclude]);
            }
        } else {
            foreach ($exclude as $one_exclusion) {
                if ($one_exclusion && isset($adjustments[$one_exclusion])) {
                    $full_amount -= $adjustments[$one_exclusion];
                    unset($adjustments[$one_exclusion]);
                }
            }
        }
        return $this->amount_factory->create($full_amount, $adjustments);
    }
    /**
     * Create selection price list for the retrieved options
     *
     * @param Option $option
     * @param Product $bundleProduct
     * @param bool $useRegularPrice
     * @return BundleSelectionPrice[]
     */
    public function create_selection_price_list($option, $bundle_product, $use_regular_price = false)
    {
        $price_list = [];
        $selections = $option->get_selections();
        if ($selections === null) {
            return $price_list;
        }
        /* @var $selection \Magento\Bundle\Model\Selection|\Magento\Catalog\Model\Product */
        foreach ($selections as $selection) {
            if (!$selection->is_salable()) {
                // @todo CatalogInventory Show out of stock Products
                continue;
            }
            $price_list[] = $this->selection_factory->create($bundle_product, $selection, $selection->get_selection_qty(), ['useRegularPrice' => $use_regular_price]);
        }
        return $price_list;
    }
    /**
     * Find minimal or maximal price for existing options
     *
     * @param Option $option
     * @param BundleSelectionPrice[] $selectionPriceList
     * @param bool $searchMin
     * @return BundleSelectionPrice[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process_options($option, $selection_price_list, $search_min = true)
    {
        $result = [];
        foreach ($selection_price_list as $current) {
            $qty = $current->get_quantity();
            $current_value = $current->get_amount()->get_value() * $qty;
            if (empty($result)) {
                $result = [$current];
            } else {
                $last_selection_price = end($result);
                $last_value = $last_selection_price->get_amount()->get_value() * $last_selection_price->get_quantity();
                if ($search_min && $last_value > $current_value) {
                    $result = [$current];
                } elseif (!$search_min && $option->is_multi_selection()) {
                    $result[] = $current;
                } elseif (!$search_min && !$option->is_multi_selection() && $last_value < $current_value) {
                    $result = [$current];
                }
            }
        }
        return $result;
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->option_amount = [];
    }
}