<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as BundleType;
/**
 * Service to check is bundle product has single choice (no customization possible)
 */
class Single_Choice_Provider
{
    /**
     * Single choice availability
     *
     * @param Product $product
     * @return bool
     */
    public function is_single_choice_available(Product $product): bool
    {
        $result = false;
        if ($product->get_type_id() === Bundle_Type::TYPE_BUNDLE) {
            $type_instance = $product->get_type_instance();
            $type_instance->set_store_filter($product->get_store_id(), $product);
            if ($type_instance->has_required_options($product)) {
                $options = $type_instance->get_options($product);
                $is_no_customizations = true;
                foreach ($options as $option) {
                    $option_id = $option->get_id();
                    $required = $option->get_required();
                    if ($is_no_customizations && (int) $required === 1) {
                        $selections_collection = $type_instance->get_selections_collection([$option_id], $product);
                        $selections = $selections_collection->export_to_array();
                        if (count($selections) > 1) {
                            foreach ($selections as $selection) {
                                if ($is_no_customizations) {
                                    $is_no_customizations = (int) $selection['is_default'] === 1 && (int) $selection['selection_can_change_qty'] === 0;
                                } else {
                                    break;
                                }
                            }
                        }
                    } else {
                        $is_no_customizations = false;
                        break;
                    }
                }
                $result = $is_no_customizations;
            }
        }
        return $result;
    }
}