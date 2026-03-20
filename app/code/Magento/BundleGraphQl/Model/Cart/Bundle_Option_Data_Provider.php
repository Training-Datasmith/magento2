<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Cart;

use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Bundle\Model\Product\Original_Price;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Graph_Ql\Query\Uid;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\Serializer_Interface;
use Magento\Quote\Model\Quote\Item;
/**
 * Data provider for bundled product options
 */
class Bundle_Option_Data_Provider
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'bundle';
    /** @var Uid */
    private $uid_encoder;
    /**
     * @param Data $pricingHelper
     * @param SerializerInterface $serializer
     * @param Configuration $configuration
     * @param OriginalPrice $originalPrice
     * @param Uid|null $uidEncoder
     */
    public function __construct(private readonly Data $pricing_helper, private readonly Serializer_Interface $serializer, private readonly Configuration $configuration, private readonly Original_Price $original_price, ?Uid $uid_encoder = null)
    {
        $this->uid_encoder = $uid_encoder ?: Object_Manager::get_instance()->get(Uid::class);
    }
    /**
     * Extract data for a bundled cart item
     *
     * @param Item $item
     * @return array
     */
    public function get_data(Item $item): array
    {
        $options = [];
        $product = $item->get_product();
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $type_instance = $product->get_type_instance();
        $options_quote_item_option = $item->get_option_by_code('bundle_option_ids');
        $bundle_options_ids = $options_quote_item_option ? $this->serializer->unserialize($options_quote_item_option->get_value()) : [];
        if ($bundle_options_ids) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
            $options_collection = $type_instance->get_options_by_ids($bundle_options_ids, $product);
            $selections_quote_item_option = $item->get_option_by_code('bundle_selection_ids');
            $bundle_selection_ids = $this->serializer->unserialize($selections_quote_item_option->get_value());
            if (!empty($bundle_selection_ids)) {
                $selections_collection = $type_instance->get_selections_by_ids($bundle_selection_ids, $product);
                $bundle_options = $options_collection->append_selections($selections_collection, true);
                $options = $this->build_bundle_options($bundle_options, $item);
            }
        }
        return $options;
    }
    /**
     * Build bundle product options based on current selection
     *
     * @param \Magento\Bundle\Model\Option[] $bundleOptions
     * @param Item $item
     * @return array
     */
    private function build_bundle_options(array $bundle_options, Item $item): array
    {
        $options = [];
        foreach ($bundle_options as $bundle_option) {
            if (!$bundle_option->get_selections()) {
                continue;
            }
            $options[] = ['id' => $bundle_option->get_id(), 'uid' => $this->uid_encoder->encode(self::OPTION_TYPE . '/' . $bundle_option->get_id()), 'label' => $bundle_option->get_title(), 'type' => $bundle_option->get_type(), 'values' => $this->build_bundle_option_values($bundle_option->get_selections(), $item)];
        }
        return $options;
    }
    /**
     * Build bundle product option values based on current selection
     *
     * @param Product[] $selections
     * @param Item $item
     * @return array
     */
    private function build_bundle_option_values(array $selections, Item $item): array
    {
        $values = [];
        $product = $item->get_product();
        $currency_code = $item->get_quote()->get_quote_currency_code();
        foreach ($selections as $selection) {
            $qty = (float) $this->configuration->get_selection_qty($product, $selection->get_selection_id());
            if (!$qty) {
                continue;
            }
            $selection_price = $this->configuration->get_selection_final_price($item, $selection);
            $option_details = [self::OPTION_TYPE, $selection->get_data('option_id'), $selection->get_data('selection_id'), (int) $selection->get_data('selection_qty')];
            $price = $this->pricing_helper->currency($selection_price, false, false);
            $values[] = ['id' => $selection->get_selection_id(), 'uid' => $this->uid_encoder->encode(implode('/', $option_details)), 'label' => $selection->get_name(), 'quantity' => $qty, 'price' => $price, 'priceV2' => ['currency' => $currency_code, 'value' => $price], 'original_price' => ['currency' => $currency_code, 'value' => $this->original_price->get_selection_original_price($item->get_product(), $selection)]];
        }
        return $values;
    }
}