<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Bundle\Api\Data\Link_Interface_Factory as LinkFactory;
use Magento\Bundle\Api\Data\Option_Interface_Factory as OptionFactory;
use Magento\Catalog\Api\Data\Product_Custom_Option_Interface_Factory;
use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Catalog\Api\Product_Repository_Interface as ProductRepository;
use Magento\Framework\App\Request_Interface;
use Magento\Store\Model\Store_Manager_Interface as StoreManager;
/**
 * Plugin class to initialize Bundle product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle
{
    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    protected $custom_option_factory;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var OptionFactory
     */
    protected $option_factory;
    /**
     * @var LinkFactory
     */
    protected $link_factory;
    /**
     * @var ProductRepository
     */
    protected $product_repository;
    /**
     * @var StoreManager
     */
    protected $store_manager;
    /**
     * @param RequestInterface $request
     * @param OptionFactory $optionFactory
     * @param LinkFactory $linkFactory
     * @param ProductRepository $productRepository
     * @param StoreManager $storeManager
     * @param ProductCustomOptionInterfaceFactory $customOptionFactory
     */
    public function __construct(Request_Interface $request, Option_Factory $option_factory, Link_Factory $link_factory, Product_Repository $product_repository, Store_Manager $store_manager, Product_Custom_Option_Interface_Factory $custom_option_factory)
    {
        $this->request = $request;
        $this->option_factory = $option_factory;
        $this->link_factory = $link_factory;
        $this->product_repository = $product_repository;
        $this->store_manager = $store_manager;
        $this->custom_option_factory = $custom_option_factory;
    }
    /**
     * Setting Bundle Items Data to product for further processing
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function after_initialize(\Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject, \Magento\Catalog\Model\Product $product)
    {
        $composite_readonly = $product->get_composite_readonly();
        $result['bundle_selections'] = $result['bundle_options'] = [];
        if (isset($this->request->get_post('bundle_options')['bundle_options'])) {
            foreach ($this->request->get_post('bundle_options')['bundle_options'] as $key => $option) {
                if (empty($option['bundle_selections'])) {
                    continue;
                }
                $result['bundle_selections'][$key] = $option['bundle_selections'];
                unset($option['bundle_selections']);
                $result['bundle_options'][$key] = $option;
            }
            if ($result['bundle_selections'] && !$composite_readonly) {
                $product->set_bundle_selections_data($result['bundle_selections']);
            }
            if ($result['bundle_options'] && !$composite_readonly) {
                $product->set_bundle_options_data($result['bundle_options']);
            }
            if (!$result['bundle_selections']) {
                $this->reset_bundle_product_options($product);
            }
            $this->process_bundle_options_data($product);
            $this->process_dynamic_options_data($product);
        } elseif (!$composite_readonly) {
            $this->reset_bundle_product_options($product);
        }
        $affect_product_selections = (bool) $this->request->get_post('affect_bundle_product_selections');
        $product->set_can_save_bundle_selections($affect_product_selections && !$composite_readonly);
        return $product;
    }
    /**
     * Process Bundle Options Data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function process_bundle_options_data(\Magento\Catalog\Model\Product $product)
    {
        $bundle_options_data = $product->get_bundle_options_data();
        if (!$bundle_options_data) {
            return;
        }
        $options = [];
        foreach ($bundle_options_data as $key => $option_data) {
            if (!empty($option_data['delete'])) {
                continue;
            }
            $option = $this->option_factory->create(['data' => $option_data]);
            $option->set_sku($product->get_sku());
            $links = [];
            $bundle_links = $product->get_bundle_selections_data();
            if (empty($bundle_links[$key])) {
                continue;
            }
            foreach ($bundle_links[$key] as $link_data) {
                if (!empty($link_data['delete'])) {
                    continue;
                }
                if (!empty($link_data['selection_id'])) {
                    $link_data['id'] = $link_data['selection_id'];
                }
                $links[] = $this->build_link($product, $link_data);
            }
            $option->set_product_links($links);
            $options[] = $option;
        }
        $extension = $product->get_extension_attributes();
        $extension->set_bundle_product_options($options);
        $product->set_extension_attributes($extension);
    }
    /**
     * Process Dynamic Options Data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function process_dynamic_options_data(\Magento\Catalog\Model\Product $product)
    {
        if ((int) $product->get_price_type() !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            return;
        }
        if ($product->get_options_readonly()) {
            return;
        }
        $product->set_can_save_custom_options(true);
        $custom_options = $product->get_product_options();
        if (!$custom_options) {
            return;
        }
        foreach (array_keys($custom_options) as $key) {
            $custom_options[$key]['is_delete'] = 1;
        }
        $new_options = $product->get_options();
        foreach ($custom_options as $custom_option_data) {
            if ((bool) $custom_option_data['is_delete']) {
                continue;
            }
            $custom_option = $this->custom_option_factory->create(['data' => $custom_option_data]);
            $custom_option->set_product_sku($product->get_sku());
            $new_options[] = $custom_option;
        }
        $product->set_options($new_options);
    }
    /**
     * Build product link
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $linkData
     * @return \Magento\Bundle\Api\Data\LinkInterface
     */
    private function build_link(\Magento\Catalog\Model\Product $product, array $link_data)
    {
        $link = $this->link_factory->create(['data' => $link_data]);
        if ((int) $product->get_price_type() !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            if (array_key_exists('selection_price_value', $link_data)) {
                $link->set_price($link_data['selection_price_value']);
            }
            if (array_key_exists('selection_price_type', $link_data)) {
                $link->set_price_type($link_data['selection_price_type']);
            }
        }
        $link_product = $this->product_repository->get_by_id($link_data['product_id']);
        $link->set_sku($link_product->get_sku());
        $link->set_qty($link_data['selection_qty']);
        if (array_key_exists('selection_can_change_qty', $link_data)) {
            $link->set_can_change_quantity($link_data['selection_can_change_qty']);
        }
        return $link;
    }
    /**
     * Resets bundle product options inside product extension attributes
     *
     * @param ProductInterface $product
     * @return void
     */
    private function reset_bundle_product_options(Product_Interface $product): void
    {
        $extension = $product->get_extension_attributes();
        $extension->set_bundle_product_options([]);
        $product->set_extension_attributes($extension);
        $product->set_drop_options(true);
    }
}