<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type\Bundle;

use Magento\Catalog\Model\Product;
/**
 * Bundle option renderer
 * @api
 * @since 100.0.2
 */
class Option extends \Magento\Bundle\Block\Catalog\Product\Price
{
    /**
     * Store pre-configured options
     *
     * @var int|array|string
     */
    protected $_selected_options;
    /**
     * Show if option has a single selection
     *
     * @var bool
     */
    protected $_show_single;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricing_helper;
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_tax_helper;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalog_helper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Json\Encoder_Interface $json_encoder, \Magento\Catalog\Helper\Data $catalog_data, \Magento\Framework\Registry $registry, \Magento\Framework\Stdlib\String_Utils $string, \Magento\Framework\Math\Random $math_random, \Magento\Checkout\Helper\Cart $cart_helper, \Magento\Tax\Helper\Data $tax_data, \Magento\Framework\Pricing\Helper\Data $pricing_helper, array $data = [])
    {
        $this->pricing_helper = $pricing_helper;
        $this->_catalog_helper = $catalog_data;
        $this->_tax_helper = $tax_data;
        parent::__construct($context, $json_encoder, $catalog_data, $registry, $string, $math_random, $cart_helper, $tax_data, $data);
    }
    /**
     * Check if option has a single selection
     *
     * @return bool
     */
    public function show_single()
    {
        if ($this->_show_single === null) {
            $option = $this->get_option();
            $selections = $option->get_selections();
            $this->_show_single = count($selections) == 1 && $option->get_required();
        }
        return $this->_show_single;
    }
    /**
     * Retrieve default values for template
     *
     * @return array
     */
    public function get_default_values()
    {
        $option = $this->get_option();
        $default = $option->get_default_selection();
        $selections = $option->get_selections();
        $selected_options = $this->_get_selected_options();
        $in_pre_configured = $this->get_product()->has_preconfigured_values() && $this->get_product()->get_preconfigured_values()->get_data('bundle_option_qty/' . $option->get_id());
        if (empty($selected_options) && $default) {
            $default_qty = $default->get_selection_qty() * 1;
            $can_change_qty = $default->get_selection_can_change_qty();
        } elseif (!$in_pre_configured && $selected_options && is_numeric($selected_options)) {
            $selected_selection = $option->get_selection_by_id($selected_options);
            $default_qty = $selected_selection->get_selection_qty() * 1;
            $can_change_qty = $selected_selection->get_selection_can_change_qty();
        } elseif (!$this->show_single() || $in_pre_configured) {
            $default_qty = $this->_get_selected_qty();
            $can_change_qty = (bool) $default_qty;
        } else {
            $default_qty = $selections[0]->get_selection_qty() * 1;
            $can_change_qty = $selections[0]->get_selection_can_change_qty();
        }
        return [$default_qty, $can_change_qty];
    }
    /**
     * Collect selected options
     *
     * @return int|array|string
     */
    protected function _get_selected_options()
    {
        if ($this->_selected_options === null) {
            $this->_selected_options = [];
            /** @var \Magento\Bundle\Model\Option $option */
            $option = $this->get_option();
            if ($this->get_product()->has_preconfigured_values()) {
                $selection_id = $this->get_product()->get_preconfigured_values()->get_data('bundle_option/' . $option->get_id());
                $this->assign_selection($option, $selection_id);
            }
        }
        return $this->_selected_options;
    }
    /**
     * Set selected options.
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param mixed $selectionId
     * @return void
     * @since 100.2.0
     */
    protected function assign_selection(\Magento\Bundle\Model\Option $option, $selection_id)
    {
        if (is_array($selection_id)) {
            $this->_selected_options = $selection_id;
        } elseif ($selection_id && $option->get_selection_by_id($selection_id)) {
            $this->_selected_options = $selection_id;
        } elseif (!$option->get_required()) {
            $this->_selected_options = 'None';
        }
    }
    /**
     * Define if selection is selected
     *
     * @param  Product $selection
     * @return bool
     */
    public function is_selected($selection)
    {
        $selected_options = $this->_get_selected_options();
        if (is_numeric($selected_options)) {
            return $selection->get_selection_id() == $selected_options;
        } elseif (is_array($selected_options) && !empty($selected_options)) {
            return in_array($selection->get_selection_id(), $selected_options);
        } elseif ($selected_options == 'None') {
            return false;
        }
        return $selection->get_is_default() && $selection->is_saleable();
    }
    /**
     * Retrieve selected option qty
     *
     * @return int
     */
    protected function _get_selected_qty()
    {
        if ($this->get_product()->has_preconfigured_values()) {
            $selected_qty = (float) $this->get_product()->get_preconfigured_values()->get_data('bundle_option_qty/' . $this->get_option()->get_id());
            if ($selected_qty < 0) {
                $selected_qty = 0;
            }
        } else {
            $selected_qty = 0;
        }
        return $selected_qty;
    }
    /**
     * Get product model
     *
     * @return Product
     */
    public function get_product()
    {
        if (!$this->has_data('product')) {
            $this->set_data('product', $this->_core_registry->registry('current_product'));
        }
        return $this->get_data('product');
    }
    /**
     * Get bundle option price title.
     *
     * @param Product $selection
     * @param bool $includeContainer
     * @return string
     */
    public function get_selection_qty_title_price($selection, $include_container = true)
    {
        $this->set_format_product($selection);
        $price_title = '<span class="product-name">' . $selection->get_selection_qty() * 1 . ' x ' . $this->escape_html($selection->get_name()) . '</span>';
        $price_title .= ' &nbsp; ' . ($include_container ? '<span class="price-notice">' : '') . '+' . $this->render_price_string($selection, $include_container) . ($include_container ? '</span>' : '');
        return $price_title;
    }
    /**
     * Get price for selection product
     *
     * @param Product $selection
     * @return int|float
     */
    public function get_selection_price($selection)
    {
        $price = 0;
        $store = $this->get_product()->get_store();
        if ($selection) {
            $price = $this->get_product()->get_price_model()->get_selection_pre_final_price($this->get_product(), $selection, 1);
            if (is_numeric($price)) {
                $price = $this->pricing_helper->currency_by_store($price, $store, false);
            }
        }
        return is_numeric($price) ? $price : 0;
    }
    /**
     * Get title price for selection product
     *
     * @param Product $selection
     * @param bool $includeContainer
     * @return string
     */
    public function get_selection_title_price($selection, $include_container = true)
    {
        $price_title = '<span class="product-name">' . $this->escape_html($selection->get_name()) . '</span>';
        $price_title .= ' &nbsp; ' . ($include_container ? '<span class="price-notice">' : '') . '+' . $this->render_price_string($selection, $include_container) . ($include_container ? '</span>' : '');
        return $price_title;
    }
    /**
     * Set JS validation container for element
     *
     * @param int $elementId
     * @param int $containerId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function set_validation_container($element_id, $container_id)
    {
        return '';
    }
    /**
     * Clear selected option when setting new option
     *
     * @param \Magento\Bundle\Model\Option $option
     * @return mixed
     */
    public function set_option(\Magento\Bundle\Model\Option $option)
    {
        $this->_selected_options = null;
        $this->_show_single = null;
        return parent::set_option($option);
    }
    /**
     * Format price string
     *
     * @param Product $selection
     * @param bool $includeContainer
     * @return string
     */
    public function render_price_string($selection, $include_container = true)
    {
        /** @var \Magento\Bundle\Pricing\Price\BundleOptionPrice $price */
        $price = $this->get_product()->get_price_info()->get_price('bundle_option');
        $amount = $price->get_option_selection_amount($selection);
        $price_html = $this->get_layout()->get_block('product.price.render.default')->render_amount($amount, $price, $selection, ['include_container' => $include_container]);
        return $price_html;
    }
}