<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model;

use Magento\Bundle\Api\Data\Bundle_Option_Interface;
use Magento\Bundle\Api\Data\Bundle_Option_Interface_Factory;
use Magento\Catalog\Api\Data\Product_Option_Interface;
use Magento\Catalog\Model\Product_Option_Processor_Interface;
use Magento\Framework\Data_Object;
use Magento\Framework\Data_Object\Factory as DataObjectFactory;
class Product_Option_Processor implements Product_Option_Processor_Interface
{
    /**
     * @var DataObjectFactory
     */
    protected $object_factory;
    /**
     * @var BundleOptionInterfaceFactory
     */
    protected $bundle_option_factory;
    /**
     * @param DataObjectFactory $objectFactory
     * @param BundleOptionInterfaceFactory $bundleOptionFactory
     */
    public function __construct(Data_Object_Factory $object_factory, Bundle_Option_Interface_Factory $bundle_option_factory)
    {
        $this->object_factory = $object_factory;
        $this->bundle_option_factory = $bundle_option_factory;
    }
    /**
     * {@inheritdoc}
     */
    public function convert_to_buy_request(Product_Option_Interface $product_option)
    {
        /** @var DataObject $request */
        $request = $this->object_factory->create();
        $bundle_options = $this->get_bundle_options($product_option);
        if (!empty($bundle_options) && is_array($bundle_options)) {
            $request_data = [];
            foreach ($bundle_options as $option) {
                /** @var BundleOptionInterface $option */
                foreach ($option->get_option_selections() as $selection) {
                    $request_data['bundle_option'][$option->get_option_id()][] = $selection;
                    $request_data['bundle_option_qty'][$option->get_option_id()] = $option->get_option_qty();
                }
            }
            $request->add_data($request_data);
        }
        return $request;
    }
    /**
     * Retrieve bundle options
     *
     * @param ProductOptionInterface $productOption
     * @return array
     */
    protected function get_bundle_options(Product_Option_Interface $product_option)
    {
        if ($product_option && $product_option->get_extension_attributes() && $product_option->get_extension_attributes()->get_bundle_options()) {
            return $product_option->get_extension_attributes()->get_bundle_options();
        }
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function convert_to_product_option(Data_Object $request)
    {
        $bundle_options = $request->get_bundle_option();
        $bundle_options_qty = $request->get_bundle_option_qty();
        if (!empty($bundle_options) && is_array($bundle_options)) {
            $data = [];
            foreach ($bundle_options as $option_id => $option_selections) {
                if (empty($option_selections)) {
                    continue;
                }
                $option_selections = is_array($option_selections) ? $option_selections : [$option_selections];
                $option_qty = isset($bundle_options_qty[$option_id]) ? $bundle_options_qty[$option_id] : 1;
                /** @var BundleOptionInterface $productOption */
                $product_option = $this->bundle_option_factory->create();
                $product_option->set_option_id($option_id);
                $product_option->set_option_selections($option_selections);
                $product_option->set_option_qty($option_qty);
                $data[] = $product_option;
            }
            return ['bundle_options' => $data];
        }
        return [];
    }
}