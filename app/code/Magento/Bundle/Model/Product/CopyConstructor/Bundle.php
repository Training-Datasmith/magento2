<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Product\Copy_Constructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
/**
 * Provides duplicating bundle options and selections
 */
class Bundle implements \Magento\Catalog\Model\Product\Copy_Constructor_Interface
{
    /**
     * Duplicating bundle options and selections
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
     */
    public function build(Product $product, Product $duplicate)
    {
        if ($product->get_type_id() != Type::TYPE_BUNDLE) {
            //do nothing if not bundle
            return;
        }
        $bundle_options = $product->get_extension_attributes()->get_bundle_product_options() ?: [];
        $duplicated_bundle_options = [];
        foreach ($bundle_options as $key => $bundle_option) {
            $duplicated_bundle_option = clone $bundle_option;
            /**
             * Set option and selection ids to 'null' in order to create new option(selection) for duplicated product,
             * but not modifying existing one, which led to lost of option(selection) in original product.
             */
            $product_links = [];
            foreach ($duplicated_bundle_option->get_product_links() ?: [] as $product_link) {
                $product_link_duplicate = clone $product_link;
                $product_link_duplicate->set_id(null);
                $product_link_duplicate->set_selection_id(null);
                $product_link_duplicate->set_option_id(null);
                $product_links[] = $product_link_duplicate;
            }
            $duplicated_bundle_option->set_product_links($product_links);
            $duplicated_bundle_option->set_option_id(null);
            $duplicated_bundle_options[$key] = $duplicated_bundle_option;
        }
        $duplicate->get_extension_attributes()->set_bundle_product_options($duplicated_bundle_options);
    }
}