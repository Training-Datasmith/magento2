<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes;

/**
 * Bundle Extended Attributes Block.
 */
class Extend extends \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
    /**
     * @var string
     */
    private $template = 'Magento_Bundle::catalog/product/edit/tab/attributes/extend.phtml';
    public const DYNAMIC = 0;
    public const FIXED = 1;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry = null;
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $form_factory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, array $data = [])
    {
        $this->_core_registry = $registry;
        parent::__construct($context, $data);
        $this->form_factory = $form_factory;
    }
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_can_edit_price(true);
        $this->set_can_read_price(true);
    }
    /**
     * Get Element Html
     *
     * @return string
     */
    public function get_element_html()
    {
        $template_file = $this->get_template_file($this->template);
        return $this->fetch_view($template_file);
    }
    /**
     * Execute method getElementHtml from parent class
     *
     * @return string
     */
    public function get_parent_element_html()
    {
        return parent::get_element_html();
    }
    /**
     * Get options.
     *
     * @return array
     */
    public function get_options()
    {
        return [['value' => '', 'label' => __('-- Select --')], ['value' => self::DYNAMIC, 'label' => __('Dynamic')], ['value' => self::FIXED, 'label' => __('Fixed')]];
    }
    /**
     * Is disabled field.
     *
     * @return bool
     */
    public function is_disabled_field()
    {
        return $this->_get_data('is_disabled_field') || $this->get_product()->get_id() && $this->get_attribute()->get_attribute_code() === 'price' || $this->get_element()->get_readonly();
    }
    /**
     * Get product.
     *
     * @return mixed
     */
    public function get_product()
    {
        if (!$this->get_data('product')) {
            $this->set_data('product', $this->_core_registry->registry('product'));
        }
        return $this->get_data('product');
    }
    /**
     * Get extended element.
     *
     * @param string $switchAttributeCode
     * @return \Magento\Framework\Data\Form\Element\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get_extended_element($switch_attribute_code)
    {
        $form = $this->form_factory->create();
        return $form->add_field($switch_attribute_code, 'select', ['name' => "product[{$switch_attribute_code}]", 'values' => $this->get_options(), 'class' => 'required-entry next-toinput', 'no_span' => true, 'disabled' => $this->is_disabled_field(), 'value' => $this->get_product()->get_data($switch_attribute_code)]);
    }
}