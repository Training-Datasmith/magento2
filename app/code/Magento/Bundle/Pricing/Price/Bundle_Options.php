<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Pricing\Adjustment\Bundle_Calculator_Interface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Framework\Pricing\Amount\Amount_Interface;
use Magento\Framework\Pricing\Saleable_Interface;
/**
 * Bundle option price calculation model.
 */
class Bundle_Options implements Reset_After_Request_Interface
{
    /**
     * @var BundleCalculatorInterface
     */
    private $calculator;
    /**
     * @var BundleSelectionFactory
     */
    private $selection_factory;
    /**
     * @var AmountInterface[]
     */
    private $option_selection_amount_cache = [];
    /**
     * @param BundleCalculatorInterface $calculator
     * @param BundleSelectionFactory $bundleSelectionFactory
     */
    public function __construct(Bundle_Calculator_Interface $calculator, Bundle_Selection_Factory $bundle_selection_factory)
    {
        $this->calculator = $calculator;
        $this->selection_factory = $bundle_selection_factory;
    }
    /**
     * Get Options with attached Selections collection.
     *
     * @param SaleableInterface $bundleProduct
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection|array
     */
    public function get_options(Saleable_Interface $bundle_product)
    {
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $type_instance = $bundle_product->get_type_instance();
        $type_instance->set_store_filter($bundle_product->get_store_id(), $bundle_product);
        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionCollection */
        $option_collection = $type_instance->get_options_collection($bundle_product);
        /** @var \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionCollection */
        $selection_collection = $type_instance->get_selections_collection($type_instance->get_options_ids($bundle_product), $bundle_product);
        $price_options = $option_collection->append_selections($selection_collection, true, false);
        return $price_options;
    }
    /**
     * Calculate maximal or minimal options value.
     *
     * @param SaleableInterface $bundleProduct
     * @param bool $searchMin
     *
     * @return float
     */
    public function calculate_options(Saleable_Interface $bundle_product, bool $search_min = true): float
    {
        $price_list = [];
        /* @var \Magento\Bundle\Model\Option $option */
        foreach ($this->get_options($bundle_product) as $option) {
            if ($search_min && !$option->get_required()) {
                continue;
            }
            /** @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice $selectionPriceList */
            $selection_price_list = $this->calculator->create_selection_price_list($option, $bundle_product);
            $selection_price_list = $this->calculator->process_options($option, $selection_price_list, $search_min);
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $price_list = array_merge($price_list, $selection_price_list);
        }
        $amount = $this->calculator->calculate_bundle_amount(0.0, $bundle_product, $price_list);
        return $amount->get_value();
    }
    /**
     * Get selection amount.
     *
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Model\Selection|Product $selection
     * @param bool $useRegularPrice
     *
     * @return AmountInterface
     */
    public function get_option_selection_amount(Product $bundle_product, $selection, bool $use_regular_price = false): Amount_Interface
    {
        $cache_key = implode('_', [$bundle_product->get_id(), $selection->get_option_id(), $selection->get_selection_id(), $use_regular_price ? 1 : 0]);
        if (!isset($this->option_selection_amount_cache[$cache_key])) {
            $selection_price = $this->selection_factory->create($bundle_product, $selection, $selection->get_selection_qty(), ['useRegularPrice' => $use_regular_price]);
            $this->option_selection_amount_cache[$cache_key] = $selection_price->get_amount();
        }
        return $this->option_selection_amount_cache[$cache_key];
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->option_selection_amount_cache = [];
    }
}