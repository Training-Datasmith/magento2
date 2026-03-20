<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Graph_Ql\Model\Order\Shipment;

use Magento\Catalog\Model\Product\Type\Abstract_Type;
use Magento\Sales\Api\Data\Shipment_Interface;
use Magento\Sales\Api\Data\Shipment_Item_Interface;
use Magento\Sales_Graph_Ql\Model\Shipment\Item\Formatter_Interface;
use Magento\Sales_Graph_Ql\Model\Shipment\Item\Shipment_Item_Formatter;
/**
 * Format Bundle shipment items for GraphQl output
 */
class Bundle_Shipment_Item_Formatter implements Formatter_Interface
{
    /**
     * @var ShipmentItemFormatter
     */
    private $item_formatter;
    /**
     * @param ShipmentItemFormatter $itemFormatter
     */
    public function __construct(Shipment_Item_Formatter $item_formatter)
    {
        $this->item_formatter = $item_formatter;
    }
    /**
     * Format bundle product shipment item
     *
     * @param ShipmentInterface $shipment
     * @param ShipmentItemInterface $item
     * @return array|null
     */
    public function format_shipment_item(Shipment_Interface $shipment, Shipment_Item_Interface $item): ?array
    {
        $order_item = $item->get_order_item();
        $shipping_type = $order_item->get_product_options()['shipment_type'] ?? null;
        if ($shipping_type == Abstract_Type::SHIPMENT_SEPARATELY && !$order_item->get_parent_item_id()) {
            //When bundle items are shipped separately the children are treated as their own items
            return null;
        }
        return $this->item_formatter->format_shipment_item($shipment, $item);
    }
}