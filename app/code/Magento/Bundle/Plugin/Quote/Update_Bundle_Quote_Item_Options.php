<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Plugin\Quote;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote_Management;
/**
 * Update bundle selection custom options
 */
class Update_Bundle_Quote_Item_Options
{
    /**
     * @var Option
     */
    private $option;
    /**
     * @param Option $option
     */
    public function __construct(Option $option)
    {
        $this->option = $option;
    }
    /**
     * Update bundle selection custom options before order is placed
     *
     * @param QuoteManagement $subject
     * @param Quote $quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before_submit(Quote_Management $subject, Quote $quote, array $order_data = []): void
    {
        foreach ($quote->get_all_visible_items() as $quote_item) {
            if ($quote_item->get_product_type() === Type::TYPE_CODE) {
                $options = $this->option->get_selection_options($quote_item->get_product());
                foreach ($quote_item->get_children() as $child_item) {
                    /** @var Item $childItem */
                    $custom_option = $child_item->get_option_by_code('selection_id');
                    $selection_id = $custom_option ? $custom_option->get_value() : null;
                    if ($selection_id && isset($options[$selection_id])) {
                        $child_item->set_options($options[$selection_id]);
                    }
                }
            }
        }
    }
}