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
 * Order shipment pdf items renderer
 */
class Shipment extends Abstract_Items
{
    /**
     * @var StringUtils
     */
    protected $string;
    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param StringUtils $string
     * @param Json $serializer
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(Context $context, Registry $registry, Data $tax_data, Filesystem $filesystem, Filter_Manager $filter_manager, String_Utils $string, Json $serializer, ?Abstract_Resource $resource = null, ?Abstract_Db $resource_collection = null, array $data = [])
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
        $item = $this->get_item();
        $pdf = $this->get_pdf();
        $page = $this->get_page();
        $this->_set_font_regular();
        $ship_items = $this->get_children($item);
        $items = array_merge([$item->get_order_item()], $item->get_order_item()->get_children_items());
        $prev_option_id = '';
        $draw_items = [];
        foreach ($items as $child_item) {
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
            if ($child_item->get_parent_item() && $prev_option_id != $attributes['option_id']) {
                $line[0] = ['font' => 'italic', 'text' => $this->string->split($attributes['option_label'], 60, true, true), 'feed' => 100];
                $draw_items[$option_id] = ['lines' => [$line], 'height' => 20];
                $line = [];
                $prev_option_id = $attributes['option_id'];
            }
            if ($this->is_shipment_separately() && $child_item->get_parent_item() || !$this->is_shipment_separately() && !$child_item->get_parent_item()) {
                if (isset($ship_items[$child_item->get_id()])) {
                    $qty = $ship_items[$child_item->get_id()]->get_qty() * 1;
                } elseif ($child_item->get_is_virtual()) {
                    $qty = __('N/A');
                } else {
                    $qty = 0;
                }
            } else {
                $qty = '';
            }
            $line[] = ['text' => $qty, 'feed' => 35];
            // draw Name
            if ($child_item->get_parent_item()) {
                $feed = 110;
                $name = $this->get_value_html($child_item);
            } else {
                $feed = 100;
                $name = $child_item->get_name();
            }
            $text = [];
            foreach ($this->string->split($name, 60, true, true) as $part) {
                $text[] = $part;
            }
            $line[] = ['text' => $text, 'feed' => $feed];
            // draw SKUs
            $text = [];
            foreach ($this->string->split($child_item->get_sku(), 25) as $part) {
                $text[] = $part;
            }
            $line[] = ['text' => $text, 'feed' => 565, 'align' => 'right'];
            $draw_items[$option_id]['lines'][] = $line;
        }
        // custom options
        $options = $item->get_order_item()->get_product_options();
        if ($options && isset($options['options'])) {
            foreach ($options['options'] as $option) {
                $lines = [];
                $lines[][] = ['text' => $this->string->split($this->filter_manager->strip_tags($option['label']), 70, true, true), 'font' => 'italic', 'feed' => 110];
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
                    $lines[][] = ['text' => $text, 'feed' => 115];
                }
                $draw_items[] = ['lines' => $lines, 'height' => 20, 'shift' => 5];
            }
        }
        $page = $pdf->draw_line_blocks($page, $draw_items, ['table_header' => true]);
        $this->set_page($page);
    }
}