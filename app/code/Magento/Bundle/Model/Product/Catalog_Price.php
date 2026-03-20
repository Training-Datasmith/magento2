<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Product;

/**
 * Price model for external catalogs
 */
class Catalog_Price implements \Magento\Catalog\Model\Product\Catalog_Price_Interface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $store_manager;
    /**
     * @var \Magento\Catalog\Model\Product\CatalogPrice
     */
    protected $common_price_model;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $core_registry;
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\CatalogPrice $commonPriceModel
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Store\Model\Store_Manager_Interface $store_manager, \Magento\Catalog\Model\Product\Catalog_Price $common_price_model, \Magento\Framework\Registry $core_registry)
    {
        $this->store_manager = $store_manager;
        $this->common_price_model = $common_price_model;
        $this->core_registry = $core_registry;
    }
    /**
     * @inheritdoc
     */
    public function get_catalog_price(\Magento\Catalog\Model\Product $product, ?\Magento\Store\Api\Data\Store_Interface $store = null, $incl_tax = false)
    {
        if ($store instanceof \Magento\Store\Api\Data\Store_Interface) {
            $current_store = $this->store_manager->get_store();
            $this->store_manager->set_current_store($store->get_id());
        }
        $this->core_registry->unregister('rule_data');
        $this->core_registry->register('rule_data', new \Magento\Framework\Data_Object(['store_id' => $product->get_store_id(), 'website_id' => $product->get_website_id(), 'customer_group_id' => $product->get_customer_group_id()]));
        $min_price = $product->get_price_model()->get_total_prices($product, 'min', $incl_tax);
        if ($store instanceof \Magento\Store\Api\Data\Store_Interface) {
            $this->store_manager->set_current_store($current_store->get_id());
        }
        return $min_price;
    }
    /**
     * Regular catalog price not applicable for bundle product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_catalog_regular_price(\Magento\Catalog\Model\Product $product)
    {
        return null;
    }
}