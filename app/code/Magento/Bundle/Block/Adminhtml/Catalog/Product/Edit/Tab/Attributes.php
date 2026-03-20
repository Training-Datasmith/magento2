<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Bundle product attributes tab
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Attributes extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes
{
    /**
     * @var SecureHtmlRenderer
     */
    protected $secure_renderer;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     * @param SecureHtmlRenderer|null $htmlRenderer
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, array $data = [], ?Secure_Html_Renderer $html_renderer = null)
    {
        parent::__construct($context, $registry, $form_factory, $data);
        $this->secure_renderer = $html_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
    }
    /**
     * Prepare attributes form of bundle product
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepare_form()
    {
        parent::_prepare_form();
        $special_price = $this->get_form()->get_element('special_price');
        if ($special_price) {
            $special_price->set_renderer($this->get_layout()->create_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Special::class)->set_disable_child(false));
            $special_price->add_class(implode(' ', ['validate-greater-than-zero', 'validate-number-range', 'number-range-0.00-100.00']));
        }
        $sku = $this->get_form()->get_element('sku');
        if ($sku) {
            $sku->set_renderer($this->get_layout()->create_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend::class)->set_disable_child(false));
        }
        $price = $this->get_form()->get_element('price');
        if ($price) {
            $price->set_renderer($this->get_layout()->create_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend::class, 'adminhtml.catalog.product.bundle.edit.tab.attributes.price')->set_disable_child(true));
        }
        $tax = $this->get_form()->get_element('tax_class_id');
        if ($tax) {
            $script_string = "\n                require(['prototype'], function(){\n                function changeTaxClassId() {\n                    if (\$('price_type').value == '" . \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC . "') {\n                        \$('tax_class_id').disabled = true;\n                        \$('tax_class_id').value = '0';\n                        \$('tax_class_id').removeClassName('required-entry');\n                        if (\$('advice-required-entry-tax_class_id')) {\n                            \$('advice-required-entry-tax_class_id').remove();\n                        }\n                    } else {\n                        \$('tax_class_id').disabled = false;\n                        " . ($tax->get_required() ? "\$('tax_class_id').addClassName('required-entry');" : '') . "\n                    }\n                }\n\n                if (\$('price_type')) {\n                    \$('price_type').observe('change', changeTaxClassId);\n                    changeTaxClassId();\n                }\n                });\n                ";
            $tax->set_after_element_html($this->secure_renderer->render_tag('script', [], $script_string, false));
        }
        $weight = $this->get_form()->get_element('weight');
        if ($weight) {
            $weight->set_renderer($this->get_layout()->create_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend::class)->set_disable_child(true));
        }
        $tier_price = $this->get_form()->get_element('tier_price');
        if ($tier_price) {
            $tier_price->set_renderer($this->get_layout()->create_block(\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Tier::class)->set_price_column_header(__('Percent Discount'))->set_price_validation('validate-greater-than-zero validate-number-range number-range-0.00-100.00'));
        }
    }
    /**
     * Get current product from registry
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function get_product()
    {
        if (!$this->get_data('product')) {
            $this->set_data('product', $this->_core_registry->registry('product'));
        }
        return $this->get_data('product');
    }
}