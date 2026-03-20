<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Checkout\Cart\Item;

use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Framework\Pricing\Price_Currency_Interface;
use Magento\Framework\View\Element\Message\Interpretation_Strategy_Interface;
/**
 * Shopping cart item render block
 *
 * @api
 * @since 100.0.2
 */
class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    /**
     * Bundle catalog product configuration
     *
     * @var Configuration
     */
    protected $_bundle_product_configuration = null;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Block\Product\ImageBuilder|\Magento\Catalog\Helper\Image $imageBuilder
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param InterpretationStrategyInterface $messageInterpretationStrategy
     * @param Configuration $bundleProductConfiguration
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Catalog\Helper\Product\Configuration $product_config, \Magento\Checkout\Model\Session $checkout_session, \Magento\Catalog\Block\Product\Image_Builder $image_builder, \Magento\Framework\Url\Helper\Data $url_helper, \Magento\Framework\Message\Manager_Interface $message_manager, Price_Currency_Interface $price_currency, \Magento\Framework\Module\Manager $module_manager, Interpretation_Strategy_Interface $message_interpretation_strategy, Configuration $bundle_product_configuration, array $data = [])
    {
        $this->_bundle_product_configuration = $bundle_product_configuration;
        parent::__construct($context, $product_config, $checkout_session, $image_builder, $url_helper, $message_manager, $price_currency, $module_manager, $message_interpretation_strategy, $data);
        $this->_is_scope_private = true;
    }
    /**
     * Overloaded method for getting list of bundle options
     *
     * Caches result in quote item, because it can be used in cart 'recent view' and on same page in cart checkout
     *
     * @return array
     */
    public function get_option_list()
    {
        return $this->_bundle_product_configuration->get_options($this->get_item());
    }
    /**
     * Return cart item error messages
     *
     * @return array
     */
    public function get_messages()
    {
        $messages = [];
        $quote_item = $this->get_item();
        // Add basic messages occurring during this page load
        $base_messages = $quote_item->get_message(false);
        if ($base_messages) {
            foreach ($base_messages as $message) {
                $messages[] = ['text' => $message, 'type' => $quote_item->get_has_error() ? 'error' : 'notice'];
            }
        }
        return $messages;
    }
}