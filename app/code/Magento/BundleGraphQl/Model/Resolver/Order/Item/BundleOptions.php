<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Resolver\Order\Item;

use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Graph_Ql\Config\Element\Field;
use Magento\Framework\Graph_Ql\Query\Resolver\Value_Factory;
use Magento\Framework\Graph_Ql\Query\Resolver_Interface;
use Magento\Framework\Graph_Ql\Query\Uid;
use Magento\Framework\Graph_Ql\Schema\Type\Resolve_Info;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\Creditmemo_Item_Interface;
use Magento\Sales\Api\Data\Invoice_Item_Interface;
use Magento\Sales\Api\Data\Order_Item_Interface;
use Magento\Sales\Api\Data\Shipment_Item_Interface;
/**
 * Resolve bundle options items for order item
 */
class Bundle_Options implements Resolver_Interface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'bundle';
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var ValueFactory
     */
    private $value_factory;
    /** @var Uid */
    private $uid_encoder;
    /**
     * @param ValueFactory $valueFactory
     * @param Json $serializer
     * @param Uid $uidEncoder
     */
    public function __construct(Value_Factory $value_factory, Json $serializer, Uid $uid_encoder)
    {
        $this->value_factory = $value_factory;
        $this->serializer = $serializer;
        $this->uid_encoder = $uid_encoder;
    }
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, Resolve_Info $info, ?array $value = null, ?array $args = null)
    {
        return $this->value_factory->create(function () use ($value) {
            if (!isset($value['model'])) {
                throw new Localized_Exception(__('"model" value should be specified'));
            }
            if ($value['model'] instanceof Order_Item_Interface) {
                $item = $value['model'];
                return $this->get_bundle_options($item, $value);
            }
            if ($value['model'] instanceof Invoice_Item_Interface || $value['model'] instanceof Shipment_Item_Interface || $value['model'] instanceof Creditmemo_Item_Interface) {
                $item = $value['model'];
                // Have to pass down order and item to map to avoid refetching all data
                return $this->get_bundle_options($item->get_order_item(), $value);
            }
            return null;
        });
    }
    /**
     * Format bundle options and values from a parent bundle order item
     *
     * @param OrderItemInterface $item
     * @param array $formattedItem
     * @return array
     */
    private function get_bundle_options(Order_Item_Interface $item, array $formatted_item): array
    {
        $bundle_options = [];
        if ($item->get_product_type() === 'bundle') {
            $options = $item->get_product_options();
            //loop through options
            foreach ($options['bundle_options'] ?? [] as $bundle_option_id => $bundle_option) {
                $bundle_options[$bundle_option_id]['label'] = $bundle_option['label'] ?? '';
                $bundle_options[$bundle_option_id]['id'] = isset($bundle_option['option_id']) ? $this->uid_encoder->encode((string) $bundle_option['option_id']) : null;
                $bundle_options[$bundle_option_id]['uid'] = isset($bundle_option['option_id']) ? $this->uid_encoder->encode(self::OPTION_TYPE . '/' . $bundle_option['option_id']) : null;
                if (isset($bundle_option['option_id'])) {
                    $bundle_options[$bundle_option_id]['values'] = $this->format_bundle_option_items($item, $formatted_item, $bundle_option['option_id']);
                } else {
                    $bundle_options[$bundle_option_id]['values'] = [];
                }
            }
        }
        return $bundle_options;
    }
    /**
     * Format Bundle items
     *
     * @param OrderItemInterface $item
     * @param array $formattedItem
     * @param string $bundleOptionId
     * @return array
     */
    private function format_bundle_option_items(Order_Item_Interface $item, array $formatted_item, string $bundle_option_id)
    {
        $option_items = [];
        // Find the item assign to the option
        /** @var OrderItemInterface $childrenOrderItem */
        foreach ($item->get_children_items() ?? [] as $children_order_item) {
            $child_order_item_options = $children_order_item->get_product_options();
            $bundle_child_attributes = $this->serializer->unserialize($child_order_item_options['bundle_selection_attributes'] ?? '');
            // Value Id is missing from parent, so we have to match the child to parent option
            if (isset($bundle_child_attributes['option_id']) && $bundle_child_attributes['option_id'] == $bundle_option_id) {
                $options = $child_order_item_options['info_buyRequest']['bundle_option'][$bundle_child_attributes['option_id']];
                $option_details = [self::OPTION_TYPE, $bundle_child_attributes['option_id'], is_array($options) ? implode(',', $options) : $options, (int) $child_order_item_options['info_buyRequest']['qty']];
                $option_items[$children_order_item->get_item_id()] = ['id' => $this->uid_encoder->encode((string) $children_order_item->get_item_id()), 'uid' => $this->uid_encoder->encode(implode('/', $option_details)), 'product_name' => $children_order_item->get_name(), 'product_sku' => $children_order_item->get_sku(), 'quantity' => $bundle_child_attributes['qty'], 'price' => [
                    //use options price, not child price
                    'value' => $bundle_child_attributes['price'],
                    //use currency from order
                    'currency' => $formatted_item['product_sale_price']['currency'] ?? null,
                ]];
            }
        }
        return $option_items;
    }
}