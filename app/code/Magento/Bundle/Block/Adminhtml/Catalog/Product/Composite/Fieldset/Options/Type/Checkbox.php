<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Bundle option checkbox type renderer
 *
 * @api
 * @since 100.0.2
 */
class Checkbox extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Bundle::product/composite/fieldset/options/type/checkbox.phtml';
    /**
     * @var SecureHtmlRenderer
     */
    protected $secure_renderer;
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
     * @param SecureHtmlRenderer|null $htmlRenderer
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Json\Encoder_Interface $json_encoder, \Magento\Catalog\Helper\Data $catalog_data, \Magento\Framework\Registry $registry, \Magento\Framework\Stdlib\String_Utils $string, \Magento\Framework\Math\Random $math_random, \Magento\Checkout\Helper\Cart $cart_helper, \Magento\Tax\Helper\Data $tax_data, \Magento\Framework\Pricing\Helper\Data $pricing_helper, array $data = [], ?Secure_Html_Renderer $html_renderer = null)
    {
        parent::__construct($context, $json_encoder, $catalog_data, $registry, $string, $math_random, $cart_helper, $tax_data, $pricing_helper, $data);
        $this->secure_renderer = $html_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
    }
    /**
     * @inheritdoc
     */
    public function set_validation_container($element_id, $container_id)
    {
        $script_string = 'document.getElementById(\'' . $element_id . '\').advaiceContainer = \'' . $container_id . '\';';
        return $this->secure_renderer->render_tag('script', [], $script_string, false);
    }
    /**
     * @inheritdoc
     * @since 100.3.1
     */
    public function get_selection_price($selection)
    {
        $price = parent::get_selection_price($selection);
        $qty = $selection->get_selection_qty();
        return $price * $qty;
    }
}