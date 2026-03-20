<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Quote\Item;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Pricing\Price_Currency_Interface;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Bundle product options model
 */
class Option
{
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var PriceCurrencyInterface
     */
    private $price_currency;
    /**
     * @param Json $serializer
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(Json $serializer, ?Price_Currency_Interface $price_currency = null)
    {
        $this->serializer = $serializer;
        $this->price_currency = $price_currency ?? Object_Manager::get_instance()->get(Price_Currency_Interface::class);
    }
    /**
     * Get selection options for provided bundle product
     *
     * @param Product $product
     * @return array
     */
    public function get_selection_options(Product $product): array
    {
        $options = [];
        $bundle_option_ids = $this->get_option_value_as_array($product, 'bundle_option_ids');
        if ($bundle_option_ids) {
            /** @var Type $typeInstance */
            $type_instance = $product->get_type_instance();
            $options_collection = $type_instance->get_options_by_ids($bundle_option_ids, $product);
            $selection_ids = $this->get_option_value_as_array($product, 'bundle_selection_ids');
            if ($selection_ids) {
                $selections_collection = $type_instance->get_selections_by_ids($selection_ids, $product);
                $options_collection->append_selections($selections_collection, true);
                foreach ($selections_collection as $selection) {
                    $selection_id = $selection->get_selection_id();
                    $options[$selection_id][] = $this->get_bundle_selection_attributes($product, $selection);
                }
            }
        }
        return $options;
    }
    /**
     * Get selection attributes for provided selection
     *
     * @param Product $product
     * @param Product $selection
     * @return array
     */
    private function get_bundle_selection_attributes(Product $product, Product $selection): array
    {
        $selection_id = $selection->get_selection_id();
        /** @var \Magento\Bundle\Model\Option $bundleOption */
        $bundle_option = $selection->get_option();
        /** @var Price $priceModel */
        $price_model = $product->get_price_model();
        $price = $price_model->get_selection_final_total_price($product, $selection, 0, 1);
        $custom_option = $product->get_custom_option('selection_qty_' . $selection_id);
        $qty = (float) ($custom_option ? $custom_option->get_value() : 0);
        return ['code' => 'bundle_selection_attributes', 'value' => $this->serializer->serialize(['price' => $this->price_currency->convert_and_round($price, $product->get_store()), 'qty' => $qty, 'option_label' => $bundle_option->get_title(), 'option_id' => $bundle_option->get_id()])];
    }
    /**
     * Get unserialized value of custom option
     *
     * @param Product $product
     * @param string $code
     * @return array
     */
    private function get_option_value_as_array(Product $product, string $code): array
    {
        $option = $product->get_custom_option($code);
        return $option && $option->get_value() ? $this->serializer->unserialize($option->get_value()) : [];
    }
}