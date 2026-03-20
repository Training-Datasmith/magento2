<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Item_Interface;
use Magento\Framework\Graph_Ql\Query\Uid;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\Serializer_Interface;
/**
 * Data provider for bundled product options
 */
class Bundle_Option_Data_Provider
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'bundle';
    /**
     * @var Data
     */
    private $pricing_helper;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var Uid
     */
    private $uid_encoder;
    /**
     * @param Data $pricingHelper
     * @param SerializerInterface $serializer
     * @param Configuration $configuration
     * @param Uid $uidEncoder
     */
    public function __construct(Data $pricing_helper, Serializer_Interface $serializer, Configuration $configuration, Uid $uid_encoder)
    {
        $this->pricing_helper = $pricing_helper;
        $this->serializer = $serializer;
        $this->configuration = $configuration;
        $this->uid_encoder = $uid_encoder;
    }
    /**
     * Extract data for a bundled item
     *
     * @param ItemInterface $item
     *
     * @return array
     */
    public function get_data(Item_Interface $item): array
    {
        $options = [];
        $product = $item->get_product();
        $options_quote_item_option = $item->get_option_by_code('bundle_option_ids');
        $bundle_options_ids = $options_quote_item_option ? $this->serializer->unserialize($options_quote_item_option->get_value()) : [];
        /** @var Type $typeInstance */
        $type_instance = $product->get_type_instance();
        if ($bundle_options_ids) {
            $selections_quote_item_option = $item->get_option_by_code('bundle_selection_ids');
            $options_collection = $type_instance->get_options_by_ids($bundle_options_ids, $product);
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
     * @param Option[] $bundleOptions
     * @param ItemInterface $item
     *
     * @return array
     */
    private function build_bundle_options(array $bundle_options, Item_Interface $item): array
    {
        $options = [];
        foreach ($bundle_options as $bundle_option) {
            if (!$bundle_option->get_selections()) {
                continue;
            }
            $option_details = [self::OPTION_TYPE, $bundle_option->get_option_id()];
            $uid_string = implode('/', $option_details);
            $options[] = ['id' => $bundle_option->get_id(), 'uid' => $this->uid_encoder->encode($uid_string), 'label' => $bundle_option->get_title(), 'type' => $bundle_option->get_type(), 'values' => $this->build_bundle_option_values($bundle_option->get_selections(), $item)];
        }
        return $options;
    }
    /**
     * Build bundle product option values based on current selection
     *
     * @param Product[] $selections
     * @param ItemInterface $item
     *
     * @return array
     */
    private function build_bundle_option_values(array $selections, Item_Interface $item): array
    {
        $product = $item->get_product();
        $values = [];
        foreach ($selections as $selection) {
            $qty = (float) $this->configuration->get_selection_qty($product, $selection->get_selection_id());
            if (!$qty) {
                continue;
            }
            $option_value_details = [self::OPTION_TYPE, $selection->get_option_id(), $selection->get_selection_id(), (int) $selection->get_selection_qty()];
            $uid_string = implode('/', $option_value_details);
            $selection_price = $this->configuration->get_selection_final_price($item, $selection);
            $values[] = ['id' => $selection->get_selection_id(), 'uid' => $this->uid_encoder->encode($uid_string), 'label' => $selection->get_name(), 'quantity' => $qty, 'price' => $this->pricing_helper->currency($selection_price, false, false)];
        }
        return $values;
    }
}