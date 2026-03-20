<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Plugin;

use Magento\Quote\Model\Quote\Item\Abstract_Item;
use Magento\Quote\Model\Quote\Item\To_Order_Item;
use Magento\Sales\Api\Data\Order_Item_Interface;
/**
 * Plugin for Magento\Quote\Model\Quote\Item\ToOrderItem
 */
class Quote_Item
{
    /**
     * Add bundle attributes to order data
     *
     * @param ToOrderItem $subject
     * @param OrderItemInterface $orderItem
     * @param AbstractItem $item
     * @param array $data
     * @return OrderItemInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_convert(To_Order_Item $subject, Order_Item_Interface $order_item, Abstract_Item $item, $data = [])
    {
        if ($attributes = $item->get_product()->get_custom_option('bundle_selection_attributes')) {
            $product_options = $order_item->get_product_options();
            $product_options['bundle_selection_attributes'] = $attributes->get_value();
            $order_item->set_product_options($product_options);
        }
        return $order_item;
    }
}