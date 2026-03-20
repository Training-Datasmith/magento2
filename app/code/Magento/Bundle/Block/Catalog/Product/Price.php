<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Catalog\Product;

/**
 * Bundle product price block
 * @api
 * @since 100.0.2
 */
class Price extends \Magento\Catalog\Block\Product\Price
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_tax_helper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Tax\Helper\Data $taxData
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Json\Encoder_Interface $json_encoder, \Magento\Catalog\Helper\Data $catalog_data, \Magento\Framework\Registry $registry, \Magento\Framework\Stdlib\String_Utils $string, \Magento\Framework\Math\Random $math_random, \Magento\Checkout\Helper\Cart $cart_helper, \Magento\Tax\Helper\Data $tax_data, array $data = [])
    {
        $this->_tax_helper = $tax_data;
        parent::__construct($context, $json_encoder, $catalog_data, $registry, $string, $math_random, $cart_helper, $data);
    }
    /**
     * Check if we have display prices including and excluding tax
     * With corrections for Dynamic prices
     *
     * @return bool
     */
    public function display_both_prices()
    {
        $product = $this->get_product();
        if ($product->get_price_type() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC && $product->get_price_model()->get_is_prices_calculated_by_index() !== false) {
            return false;
        }
        return $this->_tax_helper->display_both_prices();
    }
    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId
     * @return bool|\Magento\Store\Model\Website
     */
    public function get_website($store_id)
    {
        return $this->_store_manager->get_store($store_id)->get_website();
    }
}