<?php

/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Plugin\Quote;

use Magento\Bundle\Model\Product\Original_Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Quote\Api\Data\Shipping_Assignment_Interface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Subtotal;
/**
 * Update bundle base original price
 */
class Update_Bundle_Quote_Item_Base_Original_Price
{
    /**
     * @param OriginalPrice $price
     */
    public function __construct(private readonly Original_Price $price)
    {
    }
    /**
     * Update bundle base original price
     *
     * @param Subtotal $subject
     * @param Subtotal $result
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return Subtotal
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function after_collect(Subtotal $subject, Subtotal $result, Quote $quote, Shipping_Assignment_Interface $shipping_assignment, Total $total): Subtotal
    {
        foreach ($quote->get_all_visible_items() as $quote_item) {
            if ($quote_item->get_product_type() === Type::TYPE_CODE) {
                $price = $quote_item->get_product()->get_price();
                $price += $this->price->get_total_bundle_items_original_price($quote_item->get_product());
                $quote_item->set_base_original_price($price);
            }
        }
        return $result;
    }
}