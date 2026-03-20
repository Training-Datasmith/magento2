<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Sales\Order;

use Laminas\Validator\Validator_Interface;
use Magento\Bundle\Model\Sales\Order\Shipment\Bundle_Shipment_Type_Validator;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Request;
use Magento\Sales\Api\Data\Shipment_Item_Interface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment;
/**
 * Validate if requested order items can be shipped according to bundle product shipment type
 */
class Bundle_Order_Type_Validator extends Bundle_Shipment_Type_Validator implements Validator_Interface
{
    private const SHIPMENT_API_ROUTE = 'v1/shipment';
    public const SHIPMENT_TYPE_TOGETHER = '0';
    public const SHIPMENT_TYPE_SEPARATELY = '1';
    /**
     * @var array
     */
    private array $messages = [];
    /**
     * @var Request
     */
    private Request $request;
    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Validates shipment items based on order item properties
     *
     * @param Shipment $value
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Sales\Exception\DocumentValidationException
     */
    public function is_valid($value): bool
    {
        if (false === $this->validation_needed()) {
            return true;
        }
        $result = $shipping_info = [];
        foreach ($value->get_items() as $shipment_item) {
            $shipping_info[$shipment_item->get_order_item_id()] = ['shipment_info' => $shipment_item, 'order_info' => $value->get_order()->get_item_by_id($shipment_item->get_order_item_id())];
        }
        foreach ($shipping_info as $shipping_item_info) {
            if ($shipping_item_info['order_info']->get_product_type() === Type::TYPE_BUNDLE) {
                $result[] = $this->check_bundle_item($shipping_item_info, $shipping_info);
            } elseif ($shipping_item_info['order_info']->get_parent_item() && $shipping_item_info['order_info']->get_parent_item()->get_product_type() === Type::TYPE_BUNDLE) {
                $result[] = $this->check_child_item($shipping_item_info['order_info'], $shipping_info);
            }
            $this->render_validation_messages($result);
        }
        return empty($this->messages);
    }
    /**
     * Returns validation messages
     *
     * @return array|string[]
     */
    public function get_messages(): array
    {
        return $this->messages;
    }
    /**
     * Checks if shipment child item can be processed
     *
     * @param Item $orderItem
     * @param array $shipmentInfo
     * @return Phrase|null
     * @throws NoSuchEntityException
     */
    private function check_child_item(Item $order_item, array $shipment_info): ?Phrase
    {
        $result = null;
        if ($order_item->get_parent_item()->get_product_type() === Type::TYPE_BUNDLE && $order_item->get_parent_item()->get_product()->get_shipment_type() === self::SHIPMENT_TYPE_TOGETHER) {
            $result = __('Cannot create shipment as bundle product "%1" has shipment type "%2". ' . '%3 should be shipped instead.', $order_item->get_parent_item()->get_sku(), __('Together'), __('Bundle product itself'));
        }
        if ($order_item->get_parent_item()->get_product_type() === Type::TYPE_BUNDLE && $order_item->get_parent_item()->get_product()->get_shipment_type() === self::SHIPMENT_TYPE_SEPARATELY && false === $this->has_parent_in_shipping($order_item, $shipment_info)) {
            $result = __('Cannot create shipment as bundle product %1 should be included as well.', $order_item->get_parent_item()->get_sku());
        }
        return $result;
    }
    /**
     * Checks if bundle item can be processed as a shipment item
     *
     * @param array $shippingItemInfo
     * @param array $shippingInfo
     * @return Phrase|null
     */
    private function check_bundle_item(array $shipping_item_info, array $shipping_info): ?Phrase
    {
        $result = null;
        /** @var Item $orderItem */
        $order_item = $shipping_item_info['order_info'];
        /** @var ShipmentItemInterface $shipmentItem */
        $shipment_item = $shipping_item_info['shipment_info'];
        if ($order_item->get_product()->get_shipment_type() === self::SHIPMENT_TYPE_TOGETHER && $this->has_children_in_shipping($shipment_item, $shipping_info)) {
            $result = __('Cannot create shipment as bundle product "%1" has shipment type "%2". ' . '%3 should be shipped instead.', $order_item->get_sku(), __('Together'), __('Bundle product itself'));
        }
        if ($order_item->get_product()->get_shipment_type() === self::SHIPMENT_TYPE_SEPARATELY && false === $this->has_children_in_shipping($shipment_item, $shipping_info)) {
            $result = __('Cannot create shipment as bundle product "%1" has shipment type "%2". ' . 'Shipment should also incorporate bundle options.', $order_item->get_sku(), __('Separately'));
        }
        return $result;
    }
    /**
     * Determines if a child shipment item has its corresponding parent in shipment
     *
     * @param Item $childItem
     * @param array $shipmentInfo
     * @return bool
     */
    private function has_parent_in_shipping(Item $child_item, array $shipment_info): bool
    {
        /** @var Item $orderItem */
        foreach (array_column($shipment_info, 'order_info') as $order_item) {
            if (!$order_item->get_parent_item_id() && $order_item->get_product_type() === Type::TYPE_BUNDLE && $child_item->get_parent_item_id() == $order_item->get_item_id()) {
                return true;
            }
        }
        return false;
    }
    /**
     * Determines if a bundle shipment item has at least one child in shipment
     *
     * @param ShipmentItemInterface $bundleItem
     * @param array $shippingInfo
     * @return bool
     */
    private function has_children_in_shipping(Shipment_Item_Interface $bundle_item, array $shipping_info): bool
    {
        /** @var Item $orderItem */
        foreach (array_column($shipping_info, 'order_info') as $order_item) {
            if ($order_item->get_parent_item_id() && $order_item->get_parent_item_id() == $bundle_item->get_order_item_id()) {
                return true;
            }
        }
        return false;
    }
    /**
     * Determines if the validation should be triggered or not
     *
     * @return bool
     */
    private function validation_needed(): bool
    {
        return str_contains(strtolower($this->request->get_uri()->get_path()), self::SHIPMENT_API_ROUTE);
    }
    /**
     * Creates text based validation messages
     *
     * @param array $validationMessages
     * @return void
     */
    private function render_validation_messages(array $validation_messages): void
    {
        foreach ($validation_messages as $message) {
            if ($message instanceof Phrase) {
                $this->messages[] = $message->render();
            }
        }
        $this->messages = array_unique($this->messages);
    }
}