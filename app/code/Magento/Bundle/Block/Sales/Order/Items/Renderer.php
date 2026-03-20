<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Sales\Order\Items;

use Magento\Catalog\Model\Product\Type\Abstract_Type;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Order item render block
 * @api
 * @since 100.0.2
 */
class Renderer extends \Magento\Sales\Block\Order\Item\Renderer\Default_Renderer
{
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Stdlib\String_Utils $string, \Magento\Catalog\Model\Product\Option_Factory $product_option_factory, array $data = [], ?Json $serializer = null)
    {
        $this->serializer = $serializer ?: \Magento\Framework\App\Object_Manager::get_instance()->get(Json::class);
        parent::__construct($context, $string, $product_option_factory, $data);
    }
    /**
     * Check if shipment type (invoice etc) is separate
     *
     * @param mixed $item
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function is_shipment_separately($item = null)
    {
        if ($item) {
            if ($item->get_order_item()) {
                $item = $item->get_order_item();
            }
            $parent_item = $item->get_parent_item();
            if ($parent_item) {
                $options = $parent_item->get_product_options();
                if ($options) {
                    return isset($options['shipment_type']) && $options['shipment_type'] == Abstract_Type::SHIPMENT_SEPARATELY;
                }
            } else {
                $options = $item->get_product_options();
                if ($options) {
                    return !(isset($options['shipment_type']) && $options['shipment_type'] == Abstract_Type::SHIPMENT_SEPARATELY);
                }
            }
        }
        $options = $this->get_order_item()->get_product_options();
        if ($options) {
            if (isset($options['shipment_type']) && $options['shipment_type'] == Abstract_Type::SHIPMENT_SEPARATELY) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if sub product calculations are present
     *
     * @param mixed $item
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function is_child_calculated($item = null)
    {
        if ($item) {
            if ($item->get_order_item()) {
                $item = $item->get_order_item();
            }
            $parent_item = $item->get_parent_item();
            if ($parent_item) {
                $options = $parent_item->get_product_options();
                if ($options) {
                    return isset($options['product_calculations']) && $options['product_calculations'] == Abstract_Type::CALCULATE_CHILD;
                }
            } else {
                $options = $item->get_product_options();
                if ($options) {
                    return !(isset($options['product_calculations']) && $options['product_calculations'] == Abstract_Type::CALCULATE_CHILD);
                }
            }
        }
        $options = $this->get_order_item()->get_product_options();
        if ($options) {
            if (isset($options['product_calculations']) && $options['product_calculations'] == Abstract_Type::CALCULATE_CHILD) {
                return true;
            }
        }
        return false;
    }
    /**
     * Get bundle selection attributes
     *
     * @param mixed $item
     *
     * @return mixed|null
     */
    public function get_selection_attributes($item)
    {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->get_product_options();
        } else {
            $options = $item->get_order_item()->get_product_options();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return $this->serializer->unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }
    /**
     * Get html of bundle selection attributes
     *
     * @param mixed $item
     *
     * @return string
     */
    public function get_value_html($item)
    {
        if ($attributes = $this->get_selection_attributes($item)) {
            return (float) $attributes['qty'] . ' x ' . $this->escape_html($item->get_name()) . ' ' . $this->get_order()->format_price($attributes['price']);
        }
        return $this->escape_html($item->get_name());
    }
    /**
     * Getting all available children for Invoice, Shipment or CreditMemo item
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     */
    public function get_children($item)
    {
        $items_array = [];
        $items = null;
        if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item) {
            $items = $item->get_invoice()->get_all_items();
        } elseif ($item instanceof \Magento\Sales\Model\Order\Shipment\Item) {
            $items = $item->get_shipment()->get_all_items();
        } elseif ($item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) {
            $items = $item->get_creditmemo()->get_all_items();
        }
        if ($items) {
            foreach ($items as $value) {
                $parent_item = $value->get_order_item()->get_parent_item();
                if ($parent_item) {
                    $items_array[$parent_item->get_id()][$value->get_order_item_id()] = $value;
                } else {
                    $items_array[$value->get_order_item()->get_id()][$value->get_order_item_id()] = $value;
                }
            }
        }
        if (isset($items_array[$item->get_order_item()->get_id()])) {
            return $items_array[$item->get_order_item()->get_id()];
        }
        return null;
    }
    /**
     * Check if price info can be shown
     *
     * @param mixed $item
     *
     * @return bool
     */
    public function can_show_price_info($item)
    {
        if ($item->get_order_item()->get_parent_item() && $this->is_child_calculated() || !$item->get_order_item()->get_parent_item() && !$this->is_child_calculated()) {
            return true;
        }
        return false;
    }
    /**
     * Get the html for item price
     *
     * @param OrderItem|InvoiceItem|CreditmemoItem $item
     * @return string
     */
    public function get_item_price($item)
    {
        $block = $this->get_layout()->get_block('item_price');
        $block->set_item($item);
        return $block->to_html();
    }
}