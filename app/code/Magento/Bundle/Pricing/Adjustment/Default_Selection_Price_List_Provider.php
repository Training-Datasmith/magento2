<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Price\Bundle_Selection_Factory;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Store\Api\Website_Repository_Interface;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * Provide lightweight implementation which uses price index
 */
class Default_Selection_Price_List_Provider implements Selection_Price_List_Provider_Interface, Reset_After_Request_Interface
{
    /**
     * @var BundleSelectionFactory
     */
    private $selection_factory;
    /**
     * @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    private $price_list;
    /**
     * @var CatalogData
     */
    private $catalog_data;
    /**
     * @var StoreManagerInterface
     */
    private $store_manager;
    /**
     * @var WebsiteRepositoryInterface
     */
    private $website_repository;
    /**
     * @param BundleSelectionFactory $bundleSelectionFactory
     * @param CatalogData $catalogData
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(Bundle_Selection_Factory $bundle_selection_factory, Catalog_Data $catalog_data, Store_Manager_Interface $store_manager, Website_Repository_Interface $website_repository)
    {
        $this->selection_factory = $bundle_selection_factory;
        $this->catalog_data = $catalog_data;
        $this->store_manager = $store_manager;
        $this->website_repository = $website_repository;
    }
    /**
     * @inheritdoc
     */
    public function get_price_list(Product $bundle_product, $search_min, $use_regular_price)
    {
        $should_find_min_option = $this->is_should_find_min_option($bundle_product, $search_min);
        $can_skip_required_options = $search_min && !$should_find_min_option;
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $type_instance = $bundle_product->get_type_instance();
        $this->price_list = [];
        foreach ($this->get_bundle_options($bundle_product) as $option) {
            /** @var Option $option */
            if ($this->can_skip_option($option, $can_skip_required_options)) {
                continue;
            }
            $selections_collection = $type_instance->get_selections_collection([(int) $option->get_option_id()], $bundle_product);
            if ((int) $bundle_product->get_price_type() !== Price::PRICE_TYPE_FIXED) {
                $selections_collection->set_flag('has_stock_status_filter', true);
            }
            $selections_collection->remove_attribute_to_select();
            if (!$use_regular_price) {
                $selections_collection->add_attribute_to_select('special_price');
                $selections_collection->add_attribute_to_select('special_from_date');
                $selections_collection->add_attribute_to_select('special_to_date');
                $selections_collection->add_attribute_to_select('tax_class_id');
            }
            if (!$search_min && $option->is_multi_selection()) {
                $this->add_maximum_multi_selection_price_list($bundle_product, $selections_collection, $use_regular_price);
            } else {
                $this->add_mini_max_price_list($bundle_product, $selections_collection, $search_min, $use_regular_price);
            }
        }
        if ($should_find_min_option) {
            $this->process_min_price_for_non_required_options();
        }
        return $this->price_list;
    }
    /**
     * Flag shows - is it necessary to find minimal option amount in case if all options are not required
     *
     * @param Product $bundleProduct
     * @param bool $searchMin
     * @return bool
     */
    private function is_should_find_min_option(Product $bundle_product, $search_min)
    {
        $should_find_min_option = false;
        if ($search_min && $bundle_product->get_price_type() == Price::PRICE_TYPE_DYNAMIC && !$this->has_required_option($bundle_product)) {
            $should_find_min_option = true;
        }
        return $should_find_min_option;
    }
    /**
     * Add minimum or maximum price for option
     *
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $searchMin
     * @param bool $useRegularPrice
     * @return void
     */
    private function add_mini_max_price_list(Product $bundle_product, $selections_collection, $search_min, $use_regular_price)
    {
        $selections_collection->add_price_filter($bundle_product, $search_min, $use_regular_price);
        if ($bundle_product->is_salable()) {
            $selections_collection->add_quantity_filter();
        }
        $selections_collection->set_page(0, 1);
        $selection = $selections_collection->get_first_item();
        if (!$selection->is_empty()) {
            $this->price_list[] = $this->selection_factory->create($bundle_product, $selection, $selection->get_selection_qty(), ['useRegularPrice' => $use_regular_price]);
        }
    }
    /**
     * Add maximum price for multi selection option
     *
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $useRegularPrice
     * @return void
     */
    private function add_maximum_multi_selection_price_list(Product $bundle_product, $selections_collection, $use_regular_price)
    {
        $website_id = (int) $this->store_manager->get_store()->get_website_id();
        if ($website_id === 0) {
            $website_id = $this->website_repository->get_default()->get_id();
        }
        $selections_collection->add_price_data(null, $website_id);
        foreach ($selections_collection as $selection) {
            $this->price_list[] = $this->selection_factory->create($bundle_product, $selection, $selection->get_selection_qty(), ['useRegularPrice' => $use_regular_price]);
        }
    }
    /**
     * Adjust min price for non required options
     *
     * @return void
     */
    private function process_min_price_for_non_required_options()
    {
        $min_price = null;
        $price_selection = null;
        foreach ($this->price_list as $price) {
            $min_price_tmp = $price->get_amount()->get_value() * $price->get_quantity();
            if (!$min_price || $min_price_tmp < $min_price) {
                $min_price = $min_price_tmp;
                $price_selection = $price;
            }
        }
        $this->price_list = $price_selection ? [$price_selection] : [];
    }
    /**
     * Check this option if it should be skipped
     *
     * @param Option $option
     * @param bool $canSkipRequiredOption
     * @return bool
     */
    private function can_skip_option($option, $can_skip_required_option)
    {
        return $can_skip_required_option && !$option->get_required();
    }
    /**
     * Check the bundle product for availability of required options
     *
     * @param Product $bundleProduct
     * @return bool
     */
    private function has_required_option($bundle_product)
    {
        $collection = clone $this->get_bundle_options($bundle_product);
        $collection->clear();
        return $collection->add_filter(Option::KEY_REQUIRED, 1)->get_size() > 0;
    }
    /**
     * Get bundle options
     *
     * @param Product $saleableItem
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    private function get_bundle_options(Product $saleable_item)
    {
        return $saleable_item->get_type_instance()->get_options_collection($saleable_item);
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->price_list = null;
    }
}