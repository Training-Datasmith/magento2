<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Observer;

use Magento\Framework\Event\Observer_Interface;
/**
 * Class adds bundle products into up-sell products collection
 */
class Append_Upsell_Products_Observer implements Observer_Interface
{
    /**
     * Bundle data
     *
     * @var \Magento\Bundle\Helper\Data
     */
    protected $bundle_data;
    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection
     */
    protected $bundle_selection;
    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $config;
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $product_visibility;
    /**
     * @param \Magento\Bundle\Helper\Data $bundleData
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection
     */
    public function __construct(\Magento\Bundle\Helper\Data $bundle_data, \Magento\Catalog\Model\Product\Visibility $product_visibility, \Magento\Catalog\Model\Config $config, \Magento\Bundle\Model\Resource_Model\Selection $bundle_selection)
    {
        $this->bundle_data = $bundle_data;
        $this->product_visibility = $product_visibility;
        $this->config = $config;
        $this->bundle_selection = $bundle_selection;
    }
    /**
     * Append bundles in upsell list for current product
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $observer->get_event()->get_product();
        /**
         * Check is current product type is allowed for bundle selection product type
         */
        if (!in_array($product->get_type_id(), $this->bundle_data->get_allowed_selection_types())) {
            return $this;
        }
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection */
        $collection = $observer->get_event()->get_collection();
        $limit = $observer->get_event()->get_limit();
        if (is_array($limit)) {
            if (isset($limit['upsell'])) {
                $limit = $limit['upsell'];
            } else {
                $limit = 0;
            }
        }
        /* @var $resource \Magento\Bundle\Model\ResourceModel\Selection */
        $resource = $this->bundle_selection;
        $product_ids = array_keys($collection->get_items());
        if ($limit !== null && $limit <= count($product_ids)) {
            return $this;
        }
        // retrieve bundle product ids
        $bundle_ids = $resource->get_parent_ids_by_child($product->get_id());
        // exclude up-sell product ids
        $bundle_ids = array_diff($bundle_ids, $product_ids);
        if (!$bundle_ids) {
            return $this;
        }
        /* @var $bundleCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $bundle_collection = $product->get_collection();
        $bundle_collection->add_attribute_to_select($this->config->get_product_attributes());
        $bundle_collection->add_store_filter();
        $bundle_collection->add_minimal_price();
        $bundle_collection->add_final_price();
        $bundle_collection->add_tax_percents();
        $bundle_collection->set_visibility($this->product_visibility->get_visible_in_catalog_ids());
        if ($limit !== null) {
            $bundle_collection->set_page_size($limit);
        }
        $bundle_collection->add_field_to_filter('entity_id', ['in' => $bundle_ids])->set_flag('do_not_use_category_id', true);
        if ($collection instanceof \Magento\Framework\Data\Collection) {
            foreach ($bundle_collection as $item) {
                $collection->add_item($item);
            }
        } elseif ($collection instanceof \Magento\Framework\Data_Object) {
            $items = $collection->get_items();
            foreach ($bundle_collection as $item) {
                $items[$item->get_entity_id()] = $item;
            }
            $collection->set_items($items);
        }
        return $this;
    }
}