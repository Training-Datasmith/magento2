<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Sales\Order\Items;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product\Type\Abstract_Type;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Adminhtml sales order item renderer
 *
 * @api
 * @since 100.0.2
 */
class Renderer extends \Magento\Sales\Block\Adminhtml\Items\Renderer\Default_Renderer
{
    /**
     * Serializer interface instance.
     *
     * @var Json
     */
    private $serializer;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param CatalogHelper|null $catalogHelper
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Catalog_Inventory\Api\Stock_Registry_Interface $stock_registry, \Magento\Catalog_Inventory\Api\Stock_Configuration_Interface $stock_configuration, \Magento\Framework\Registry $registry, array $data = [], ?Json $serializer = null, ?Catalog_Helper $catalog_helper = null)
    {
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Json::class);
        $data['catalogHelper'] = $catalog_helper ?? Object_Manager::get_instance()->get(Catalog_Helper::class);
        parent::__construct($context, $stock_registry, $stock_configuration, $registry, $data);
    }
    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string $remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncate_string($value, $length = 80, $etc = '...', &$remainder = '', $break_words = true)
    {
        return $this->filter_manager->truncate($value, ['length' => $length, 'etc' => $etc, 'remainder' => $remainder, 'breakWords' => $break_words]);
    }
    /**
     * Getting all available children for Invoice, Shipment or CreditMemo item
     *
     * @param \Magento\Framework\DataObject $item
     * @return array|null
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
            $items_array[(string) $item->get_order_item()->get_id()][(string) $item->get_order_item_id()] = $item;
            foreach ($items as $value) {
                $parent_item = $value->get_order_item()->get_parent_item();
                if ($parent_item) {
                    $items_array[(string) $parent_item->get_id()][(string) $value->get_order_item_id()] = $value;
                } else {
                    $items_array[(string) $value->get_order_item()->get_id()][(string) $value->get_order_item_id()] = $value;
                }
            }
        }
        $order_item_id = (string) $item->get_order_item()->get_id();
        if (isset($items_array[$order_item_id])) {
            return $items_array[$order_item_id];
        }
        return null;
    }
    /**
     * Check if item can be shipped separately
     *
     * @param mixed $item
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
     * Check if child items calculated
     *
     * @param mixed $item
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
     * Retrieve selection attributes values
     *
     * @param mixed $item
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
     * Retrieve order item options array
     *
     * @return array
     */
    public function get_order_options()
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
     * Retrieve order item
     *
     * @return mixed
     */
    public function get_order_item()
    {
        if ($this->get_item() instanceof \Magento\Sales\Model\Order\Item) {
            return $this->get_item();
        }
        return $this->get_item()->get_order_item();
    }
    /**
     * Get html info for item
     *
     * @param mixed $item
     * @return string
     */
    public function get_value_html($item)
    {
        $result = $this->escape_html($item->get_name());
        if (!$this->is_shipment_separately($item)) {
            $attributes = $this->get_selection_attributes($item);
            if ($attributes) {
                $result = (float) $attributes['qty'] . ' x ' . $result;
            }
        }
        if (!$this->is_child_calculated($item)) {
            $attributes = $this->get_selection_attributes($item);
            if ($attributes) {
                $result .= ' ' . $this->get_order_item()->get_order()->format_price($attributes['price']);
            }
        }
        return $result;
    }
    /**
     * Check if we can show price info for this item
     *
     * @param object $item
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