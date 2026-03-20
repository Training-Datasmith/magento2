<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\Filter_Manager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\Resource_Model\Abstract_Resource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\String_Utils;
use Magento\Tax\Helper\Data;
/**
 * Order creditmemo pdf default items renderer
 */
class Creditmemo extends Abstract_Items
{
    /**
     * Core string
     *
     * @var StringUtils
     */
    protected $string;
    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param Json $serializer
     * @param StringUtils $string
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(Context $context, Registry $registry, Data $tax_data, Filesystem $filesystem, Filter_Manager $filter_manager, Json $serializer, String_Utils $string, ?Abstract_Resource $resource = null, ?Abstract_Db $resource_collection = null, array $data = [])
    {
        $this->string = $string;
        parent::__construct($context, $registry, $tax_data, $filesystem, $filter_manager, $serializer, $resource, $resource_collection, $data);
    }
    /**
     * Draw item line
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function draw()
    {
        $order = $this->get_order();
        $item = $this->get_item();
        $pdf = $this->get_pdf();
        $page = $this->get_page();
        $items = $this->get_children($item);
        $prev_option_id = '';
        $draw_items = [];
        $left_bound = 35;
        $right_bound = 565;
        foreach ($items as $child_item) {
            $x = $left_bound;
            $line = [];
            $attributes = $this->get_selection_attributes($child_item);
            if (is_array($attributes)) {
                $option_id = $attributes['option_id'];
            } else {
                $option_id = 0;
            }
            if (!isset($draw_items[$option_id])) {
                $draw_items[$option_id] = ['lines' => [], 'height' => 20];
            }
            // draw selection attributes
            if ($child_item->get_order_item()->get_parent_item() && $prev_option_id != $attributes['option_id']) {
                $line[0] = ['font' => 'italic', 'text' => $this->string->split($attributes['option_label'], 38, true, true), 'feed' => $x];
                $draw_items[$option_id] = ['lines' => [$line], 'height' => 20];
                $line = [];
                $prev_option_id = $attributes['option_id'];
            }
            // draw product titles
            if ($child_item->get_order_item()->get_parent_item()) {
                $feed = $x + 5;
                $name = $this->get_value_html($child_item);
            } else {
                $feed = $x;
                $name = $child_item->get_name();
            }
            $line[] = ['text' => $this->string->split($name, 35, true, true), 'feed' => $feed];
            $x += 220;
            // draw SKUs
            if (!$child_item->get_order_item()->get_parent_item()) {
                $text = [];
                foreach ($this->string->split($item->get_sku(), 17) as $part) {
                    $text[] = $part;
                }
                $line[] = ['text' => $text, 'feed' => $x, 'align' => 'right'];
            }
            $x += 30;
            // draw prices
            if ($this->can_show_price_info($child_item)) {
                // draw Total(ex)
                $text = $order->format_price_txt($child_item->get_row_total());
                $line[] = ['text' => $text, 'feed' => $x, 'font' => 'bold', 'align' => 'right', 'width' => 50];
                $x += 50;
                // draw Discount
                $text = $order->format_price_txt(-$child_item->get_discount_amount());
                $line[] = ['text' => $text, 'feed' => $x, 'font' => 'bold', 'align' => 'right', 'width' => 50];
                $x += 85;
                // draw QTY
                $text = $child_item->get_qty() * 1;
                $line[] = ['text' => $text, 'feed' => $x, 'font' => 'bold', 'align' => 'right', 'width' => 30];
                $x += 35;
                // draw Tax
                $text = $order->format_price_txt($child_item->get_tax_amount());
                $line[] = ['text' => $text, 'feed' => $x, 'font' => 'bold', 'align' => 'right', 'width' => 45];
                $x += 45;
                // draw Total(inc)
                $text = $order->format_price_txt($child_item->get_row_total() + $child_item->get_tax_amount() - $child_item->get_discount_amount());
                $line[] = ['text' => $text, 'feed' => $right_bound, 'font' => 'bold', 'align' => 'right'];
            }
            $draw_items[$option_id]['lines'][] = $line;
        }
        // custom options
        $options = $item->get_order_item()->get_product_options();
        if ($options && isset($options['options'])) {
            foreach ($options['options'] as $option) {
                $lines = [];
                $lines[][] = ['text' => $this->string->split($this->filter_manager->strip_tags($option['label']), 40, true, true), 'font' => 'italic', 'feed' => $left_bound];
                if ($option['value']) {
                    $text = [];
                    $print_value = $option['print_value'] ?? $this->filter_manager->strip_tags($option['value']);
                    $print_value = str_replace(PHP_EOL, ', ', $print_value);
                    $values = explode(', ', $print_value);
                    foreach ($values as $value) {
                        foreach ($this->string->split($value, 50, true, true) as $sub_value) {
                            $text[] = $sub_value;
                        }
                    }
                    $lines[][] = ['text' => $text, 'feed' => $left_bound + 5];
                }
                $draw_items[] = ['lines' => $lines, 'height' => 20, 'shift' => 5];
            }
        }
        $page = $pdf->draw_line_blocks($page, $draw_items, ['table_header' => true]);
        $this->set_page($page);
    }
}