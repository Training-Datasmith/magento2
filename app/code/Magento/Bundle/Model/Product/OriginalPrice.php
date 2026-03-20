<?php

/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Get original price for bundle products
 */
class Original_Price
{
    /**
     * @param Json $serializer
     */
    public function __construct(private readonly Json $serializer)
    {
    }
    /**
     * Get Original Total price for Bundle items
     *
     * @param Product $product
     * @return float
     */
    public function get_total_bundle_items_original_price(Product $product): float
    {
        $price = 0.0;
        if (!$product->has_custom_options()) {
            return $price;
        }
        $selection_ids = $this->get_bundle_selection_ids($product);
        if (empty($selection_ids)) {
            return $price;
        }
        $selections = $product->get_type_instance()->get_selections_by_ids($selection_ids, $product);
        foreach ($selections->get_items() as $selection) {
            if (!$selection->is_salable()) {
                continue;
            }
            $selection_qty = $product->get_custom_option('selection_qty_' . $selection->get_selection_id());
            if ($selection_qty) {
                $price += $this->get_selection_original_total_price($product, $selection, (float) $selection_qty->get_value());
            }
        }
        return $price;
    }
    /**
     * Calculate total original price of selection
     *
     * @param Product $bundleProduct
     * @param Product $selectionProduct
     * @param float $selectionQty
     *
     * @return float
     */
    private function get_selection_original_total_price(Product $bundle_product, Product $selection_product, float $selection_qty): float
    {
        $price = $this->get_selection_original_price($bundle_product, $selection_product);
        return $price * $selection_qty;
    }
    /**
     * Calculate the original price of selection
     *
     * @param Product $bundleProduct
     * @param Product $selectionProduct
     *
     * @return float
     */
    public function get_selection_original_price(Product $bundle_product, Product $selection_product): float
    {
        if ($bundle_product->get_price_type() == Price::PRICE_TYPE_DYNAMIC) {
            return (float) $selection_product->get_price();
        }
        if ($selection_product->get_selection_price_type()) {
            // percent
            return $bundle_product->get_price() * ($selection_product->get_selection_price_value() / 100);
        }
        // fixed
        return (float) $selection_product->get_selection_price_value();
    }
    /**
     * Retrieve array of bundle selection IDs
     *
     * @param Product $product
     * @return array
     */
    private function get_bundle_selection_ids(Product $product): array
    {
        $custom_option = $product->get_custom_option('bundle_selection_ids');
        if ($custom_option) {
            $selection_ids = $this->serializer->unserialize($custom_option->get_value());
            if (is_array($selection_ids)) {
                return $selection_ids;
            }
        }
        return [];
    }
}