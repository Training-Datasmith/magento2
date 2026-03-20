<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items;

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
class Renderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\Default_Renderer
{
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param CatalogHelper|null $catalogHelper
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Catalog_Inventory\Api\Stock_Registry_Interface $stock_registry, \Magento\Catalog_Inventory\Api\Stock_Configuration_Interface $stock_configuration, \Magento\Framework\Registry $registry, \Magento\Gift_Message\Helper\Message $message_helper, \Magento\Checkout\Helper\Data $checkout_helper, array $data = [], ?Json $serializer = null, ?Catalog_Helper $catalog_helper = null)
    {
        $this->serializer = $serializer ?? Object_Manager::get_instance()->get(Json::class);
        $data['catalogHelper'] = $catalog_helper ?? Object_Manager::get_instance()->get(Catalog_Helper::class);
        parent::__construct($context, $stock_registry, $stock_configuration, $registry, $message_helper, $checkout_helper, $data);
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
     * Get is shipment separately.
     *
     * @param null|object $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function is_shipment_separately($item = null)
    {
        if ($item) {
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
        $options = $this->get_item()->get_product_options();
        if ($options) {
            if (isset($options['shipment_type']) && $options['shipment_type'] == Abstract_Type::SHIPMENT_SEPARATELY) {
                return true;
            }
        }
        return false;
    }
    /**
     * Get is child calculated.
     *
     * @param null|object $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function is_child_calculated($item = null)
    {
        if ($item) {
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
        $options = $this->get_item()->get_product_options();
        if ($options) {
            if (isset($options['product_calculations']) && $options['product_calculations'] == Abstract_Type::CALCULATE_CHILD) {
                return true;
            }
        }
        return false;
    }
    /**
     * Return selection attributes.
     *
     * @param mixed $item
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
     * Return order options.
     *
     * @return array
     */
    public function get_order_options()
    {
        $result = [];
        $options = $this->get_item()->get_product_options();
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
     * Return value html.
     *
     * @param object $item
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
                $result .= ' ' . $this->get_item()->get_order()->format_base_price($attributes['price']);
            }
        }
        return $result;
    }
    /**
     * Return can show price.
     *
     * @param object $item
     * @return bool
     */
    public function can_show_price_info($item)
    {
        if ($item->get_parent_item() && $this->is_child_calculated() || !$item->get_parent_item() && !$this->is_child_calculated()) {
            return true;
        }
        return false;
    }
}