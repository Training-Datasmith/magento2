<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Framework\Data_Object;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\Filter_Manager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\Resource_Model\Abstract_Resource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\String_Utils;
use Magento\Tax\Helper\Data;
/**
 * Order invoice pdf default items renderer
 */
class Invoice extends Abstract_Items
{
    /**
     * @var StringUtils
     */
    protected $string;
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param StringUtils $coreString
     * @param Json $serializer
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(Context $context, Registry $registry, Data $tax_data, Filesystem $filesystem, Filter_Manager $filter_manager, String_Utils $core_string, Json $serializer, ?Abstract_Resource $resource = null, ?Abstract_Db $resource_collection = null, array $data = [])
    {
        $this->string = $core_string;
        parent::__construct($context, $registry, $tax_data, $filesystem, $filter_manager, $serializer, $resource, $resource_collection, $data);
    }
    /**
     * Draw bundle product item line
     *
     * @return void
     */
    public function draw()
    {
        $draw = $this->draw_children_items();
        $draw = $this->draw_custom_options($draw);
        $page = $this->get_pdf()->draw_line_blocks($this->get_page(), $draw, ['table_header' => true]);
        $this->set_page($page);
    }
    /**
     * Draw bundle product children items
     *
     * @return array
     */
    private function draw_children_items(): array
    {
        $this->_set_font_regular();
        $prev_option_id = '';
        $draw_items = [];
        $option_id = 0;
        $lines = [];
        foreach ($this->get_children($this->get_item()) as $child_item) {
            $index = array_key_last($lines) !== null ? array_key_last($lines) + 1 : 0;
            $attributes = $this->get_selection_attributes($child_item);
            if (is_array($attributes)) {
                $option_id = $attributes['option_id'];
            }
            if (!isset($draw_items[$option_id])) {
                $draw_items[$option_id] = ['lines' => [], 'height' => 20];
            }
            if ($child_item->get_order_item()->get_parent_item() && $prev_option_id != $attributes['option_id']) {
                $lines[$index][] = ['font' => 'italic', 'text' => $this->string->split($attributes['option_label'], 45, true, true), 'feed' => 35];
                $index++;
                $prev_option_id = $attributes['option_id'];
            }
            /* in case Product name is longer than 80 chars - it is written in a few lines */
            if ($child_item->get_order_item()->get_parent_item()) {
                $feed = 40;
                $name = $this->get_value_html($child_item);
            } else {
                $feed = 35;
                $name = $child_item->get_name();
            }
            $lines[$index][] = ['text' => $this->string->split($name, 35, true, true), 'feed' => $feed];
            $lines = $this->draw_skus($child_item, $lines);
            $lines = $this->draw_prices($child_item, $lines);
        }
        $draw_items[$option_id]['lines'] = $lines;
        return $draw_items;
    }
    /**
     * Draw sku parts
     *
     * @param DataObject $childItem
     * @param array $lines
     * @return array
     */
    private function draw_skus(Data_Object $child_item, array $lines): array
    {
        $index = array_key_last($lines);
        if (!$child_item->get_order_item()->get_parent_item()) {
            $text = [];
            foreach ($this->string->split($this->get_item()->get_sku(), 17) as $part) {
                $text[] = $part;
            }
            $lines[$index][] = ['text' => $text, 'feed' => 290, 'align' => 'right'];
        }
        return $lines;
    }
    /**
     * Draw prices for bundle product children items
     *
     * @param DataObject $childItem
     * @param array $lines
     * @return array
     */
    private function draw_prices(Data_Object $child_item, array $lines): array
    {
        $index = array_key_last($lines);
        if ($this->can_show_price_info($child_item)) {
            $lines[$index][] = ['text' => $child_item->get_qty() * 1, 'feed' => 435, 'align' => 'right'];
            $tax = $this->get_order()->format_price_txt($child_item->get_tax_amount());
            $lines[$index][] = ['text' => $tax, 'feed' => 495, 'font' => 'bold', 'align' => 'right'];
            $item = $this->get_item();
            $this->_item = $child_item;
            $feed_price = 395;
            $feed_subtotal = $feed_price + 170;
            foreach ($this->get_item_prices_for_display() as $price_data) {
                if (isset($price_data['label'])) {
                    // draw Price label
                    $lines[$index][] = ['text' => $price_data['label'], 'feed' => $feed_price, 'align' => 'right'];
                    // draw Subtotal label
                    $lines[$index][] = ['text' => $price_data['label'], 'feed' => $feed_subtotal, 'align' => 'right'];
                    $index++;
                }
                // draw Price
                $lines[$index][] = ['text' => $price_data['price'], 'feed' => $feed_price, 'font' => 'bold', 'align' => 'right'];
                // draw Subtotal
                $lines[$index][] = ['text' => $price_data['subtotal'], 'feed' => $feed_subtotal, 'font' => 'bold', 'align' => 'right'];
                $index++;
            }
            $this->_item = $item;
        }
        return $lines;
    }
    /**
     * Draw bundle product custom options
     *
     * @param array $draw
     * @return array
     */
    private function draw_custom_options(array $draw): array
    {
        $options = $this->get_item()->get_order_item()->get_product_options();
        if ($options && isset($options['options'])) {
            foreach ($options['options'] as $option) {
                $lines = [];
                $lines[][] = ['text' => $this->string->split($this->filter_manager->strip_tags($option['label']), 40, true, true), 'font' => 'italic', 'feed' => 35];
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
                    $lines[][] = ['text' => $text, 'feed' => 40];
                }
                $draw[] = ['lines' => $lines, 'height' => 20, 'shift' => 5];
            }
        }
        return $draw;
    }
}