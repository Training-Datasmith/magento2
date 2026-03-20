<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\Calculator_Interface;
/**
 * Bundle calculator interface
 *
 * @api
 */
interface Bundle_Calculator_Interface extends Calculator_Interface
{
    /**
     * @param float|string $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_max_amount($amount, Product $saleable_item, $exclude = null);
    /**
     * @param float|string $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_max_regular_amount($amount, Product $saleable_item, $exclude = null);
    /**
     * @param float|string $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_min_regular_amount($amount, Product $saleable_item, $exclude = null);
    /**
     * Option amount calculation for saleable item
     *
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @param bool $searchMin
     * @param \Magento\Framework\Pricing\Amount\AmountInterface|null $bundleProductAmount
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_options_amount(Product $saleable_item, $exclude = null, $search_min = true, $bundle_product_amount = null);
    /**
     * Calculate amount for bundle product with all selection prices
     *
     * @param float $basePriceValue
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $selectionPriceList
     * @param null|bool|string|array $exclude code of adjustment that has to be excluded
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function calculate_bundle_amount($base_price_value, $bundle_product, $selection_price_list, $exclude = null);
    /**
     * Create selection price list for the retrieved options
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param Product $bundleProduct
     * @param bool $useRegularPrice
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    public function create_selection_price_list($option, $bundle_product, $use_regular_price = false);
    /**
     * Find minimal or maximal price for existing options
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $selectionPriceList
     * @param bool $searchMin
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    public function process_options($option, $selection_price_list, $search_min = true);
    /**
     * @param float $amount
     * @param Product $saleableItem
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function get_amount_without_option($amount, Product $saleable_item);
}