<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Sales\Order\Plugin;

/**
 * Plugin to calculate bundle item qty available for cancel
 */
class Item
{
    /**
     * Retrieve item qty available for cancel
     *
     * @param \Magento\Sales\Model\Order\Item $subject
     * @param float|integer $result
     * @return float|integer
     */
    public function after_get_qty_to_cancel(\Magento\Sales\Model\Order\Item $subject, $result)
    {
        if ($subject->get_product_type() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE || $subject->get_parent_item() && $subject->get_parent_item()->get_product_type() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $qty_to_cancel = $this->get_qty_to_cancel_bundle($subject);
            return max($qty_to_cancel, 0);
        }
        return $result;
    }
    /**
     * Retrieve item qty available for ship
     *
     * @param \Magento\Sales\Model\Order\Item $subject
     * @param float|integer $result
     * @return bool
     */
    public function after_is_processing_available(\Magento\Sales\Model\Order\Item $subject, $result)
    {
        if ($subject->get_product_type() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE || $subject->get_parent_item() && $subject->get_parent_item()->get_product_type() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            return $subject->get_simple_qty_to_ship() > $subject->get_qty_to_cancel();
        }
        return $result;
    }
    /**
     * Retrieve Bundle child item qty available for cancel
     * getQtyToShip() always returns 0 for BundleItems that ship together
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|integer
     */
    private function get_qty_to_cancel_bundle($item)
    {
        if ($item->is_dummy(true)) {
            return min($item->get_qty_to_invoice(), $item->get_simple_qty_to_ship());
        }
        return min($item->get_qty_to_invoice(), $item->get_qty_to_ship());
    }
}