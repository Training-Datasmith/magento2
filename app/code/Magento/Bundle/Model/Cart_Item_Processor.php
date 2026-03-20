<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model;

use Magento\Bundle\Api\Data\Bundle_Option_Interface_Factory;
use Magento\Quote\Api\Data as QuoteApi;
use Magento\Quote\Api\Data\Cart_Item_Interface;
use Magento\Quote\Model\Quote\Item\Cart_Item_Processor_Interface;
class Cart_Item_Processor implements Cart_Item_Processor_Interface
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $object_factory;
    /**
     * @var QuoteApi\ProductOptionExtensionFactory
     */
    protected $product_option_extension_factory;
    /**
     * @var BundleOptionInterfaceFactory
     */
    protected $bundle_option_factory;
    /**
     * @var QuoteApi\ProductOptionInterfaceFactory
     */
    protected $product_option_factory;
    /**
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param QuoteApi\ProductOptionExtensionFactory $productOptionExtensionFactory
     * @param BundleOptionInterfaceFactory $bundleOptionFactory
     * @param QuoteApi\ProductOptionInterfaceFactory $productOptionFactory
     */
    public function __construct(\Magento\Framework\Data_Object\Factory $object_factory, Quote_Api\Product_Option_Extension_Factory $product_option_extension_factory, Bundle_Option_Interface_Factory $bundle_option_factory, Quote_Api\Product_Option_Interface_Factory $product_option_factory)
    {
        $this->object_factory = $object_factory;
        $this->product_option_extension_factory = $product_option_extension_factory;
        $this->bundle_option_factory = $bundle_option_factory;
        $this->product_option_factory = $product_option_factory;
    }
    /**
     * @inheritDoc
     */
    public function convert_to_buy_request(Cart_Item_Interface $cart_item)
    {
        if ($cart_item->get_product_option() && $cart_item->get_product_option()->get_extension_attributes()) {
            $options = $cart_item->get_product_option()->get_extension_attributes()->get_bundle_options();
            if (is_array($options)) {
                $request_data = [];
                foreach ($options as $option) {
                    /** @var \Magento\Bundle\Api\Data\BundleOptionInterface $option */
                    foreach ($option->get_option_selections() as $selection) {
                        $request_data['bundle_option'][$option->get_option_id()][] = $selection;
                        $request_data['bundle_option_qty'][$option->get_option_id()] = $option->get_option_qty();
                    }
                }
                return $this->object_factory->create($request_data);
            }
        }
        return null;
    }
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function process_options(Cart_Item_Interface $cart_item)
    {
        if ($cart_item->get_product_type() !== \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            return $cart_item;
        }
        $product_options = [];
        $bundle_options = $cart_item->get_buy_request()->get_bundle_option();
        $bundle_options_qty = $cart_item->get_buy_request()->get_bundle_option_qty();
        $bundle_options_qty = is_array($bundle_options_qty) ? $bundle_options_qty : [];
        if (is_array($bundle_options)) {
            foreach ($bundle_options as $option_id => $option_selections) {
                if (empty($option_selections)) {
                    continue;
                }
                $option_selections = is_array($option_selections) ? $option_selections : [$option_selections];
                /** @var \Magento\Bundle\Api\Data\BundleOptionInterface $productOption */
                $product_option = $this->bundle_option_factory->create();
                $product_option->set_option_id($option_id);
                $product_option->set_option_selections($option_selections);
                if (isset($bundle_options_qty[$option_id])) {
                    $product_option->set_option_qty($bundle_options_qty[$option_id]);
                }
                $product_options[] = $product_option;
            }
            $extension = $this->product_option_extension_factory->create()->set_bundle_options($product_options);
            if (!$cart_item->get_product_option()) {
                $cart_item->set_product_option($this->product_option_factory->create());
            }
            $cart_item->get_product_option()->set_extension_attributes($extension);
        }
        return $cart_item;
    }
}