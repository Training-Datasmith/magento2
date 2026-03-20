<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

use Magento\Catalog\Model\Product\Type\Abstract_Type;
use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\Filter_Manager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\Resource_Model\Abstract_Resource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Tax\Helper\Data;
/**
 * Order pdf items renderer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Abstract_Items extends \Magento\Sales\Model\Order\Pdf\Items\Abstract_Items
{
    /**
     * Serializer interface instance.
     *
     * @var Json
     */
    private $serializer;
    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param Json $serializer
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, Data $tax_data, Filesystem $filesystem, Filter_Manager $filter_manager, Json $serializer, ?Abstract_Resource $resource = null, ?Abstract_Db $resource_collection = null, array $data = [])
    {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $tax_data, $filesystem, $filter_manager, $resource, $resource_collection, $data);
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
     * Retrieve is Shipment Separately flag for Item
     *
     * @param \Magento\Framework\DataObject $item
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
     * Retrieve is Child Calculated
     *
     * @param \Magento\Framework\DataObject $item
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
     * Retrieve Bundle Options
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_bundle_options($item = null)
    {
        $options = $this->get_order_item()->get_product_options();
        if ($options && isset($options['bundle_options'])) {
            return $options['bundle_options'];
        }
        return [];
    }
    /**
     * Retrieve Selection attributes
     *
     * @param \Magento\Framework\DataObject $item
     * @return mixed
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
     * Retrieve Order options
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_order_options($item = null)
    {
        $result = [];
        $options = $this->get_order_item()->get_product_options();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }
    /**
     * Retrieve Order Item
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function get_order_item()
    {
        if ($this->get_item() instanceof \Magento\Sales\Model\Order\Item) {
            return $this->get_item();
        }
        return $this->get_item()->get_order_item();
    }
    /**
     * Retrieve Value HTML
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function get_value_html($item)
    {
        $result = $this->filter_manager->strip_tags($item->get_name());
        if (!$this->is_shipment_separately($item)) {
            $attributes = $this->get_selection_attributes($item);
            if ($attributes) {
                $qty = $this->filter_manager->sprintf($attributes['qty'], ['format' => '%f']);
                $result = (float) $qty . ' x ' . $result;
            }
        }
        if (!$this->is_child_calculated($item)) {
            $attributes = $this->get_selection_attributes($item);
            if ($attributes) {
                $result .= ' ' . $this->filter_manager->strip_tags($this->get_order_item()->get_order()->format_price($attributes['price']));
            }
        }
        return $result;
    }
    /**
     * Can show price info for item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function can_show_price_info($item)
    {
        if ($item->get_order_item()->get_parent_item() && $this->is_child_calculated() || !$item->get_order_item()->get_parent_item() && !$this->is_child_calculated()) {
            return true;
        }
        return false;
    }
}