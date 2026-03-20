<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab;

/**
 * Adminhtml catalog product bundle items tab block
 */
class Bundle extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\Tab_Interface
{
    /**
     * @var mixed
     */
    protected $_product = null;
    /**
     * @var string
     */
    protected $_template = 'Magento_Bundle::product/edit/bundle.phtml';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry = null;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        $this->_core_registry = $registry;
        parent::__construct($context, $data);
    }
    /**
     * Return tab URL
     *
     * @return string
     */
    public function get_tab_url()
    {
        return $this->get_url('adminhtml/bundle_product_edit/form', ['_current' => true]);
    }
    /**
     * Return tab CSS class
     *
     * @return string
     */
    public function get_tab_class()
    {
        return 'ajax';
    }
    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->set_data('opened', true);
        $this->add_child('add_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Create New Option'), 'class' => 'add', 'id' => 'add_new_option', 'on_click' => 'bOption.add()']);
        $this->set_child('options_box', $this->get_layout()->create_block(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option::class, 'adminhtml.catalog.product.edit.tab.bundle.option'));
        return parent::_prepare_layout();
    }
    /**
     * Check block readonly
     *
     * @return boolean
     */
    public function is_readonly()
    {
        return $this->get_product()->get_composite_readonly();
    }
    /**
     * Return HTML for add button
     *
     * @return string
     */
    public function get_add_button_html()
    {
        return $this->get_child_html('add_button');
    }
    /**
     * Return HTML for options box
     *
     * @return string
     */
    public function get_options_box_html()
    {
        return $this->get_child_html('options_box');
    }
    /**
     * Return field suffix
     *
     * @return string
     */
    public function get_field_suffix()
    {
        return 'product';
    }
    /**
     * Return product from core registry
     *
     * @return mixed
     */
    public function get_product()
    {
        return $this->_core_registry->registry('product');
    }
    /**
     * Return tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function get_tab_label()
    {
        return __('Bundle Items');
    }
    /**
     * Return tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function get_tab_title()
    {
        return __('Bundle Items');
    }
    /**
     * Return true always
     *
     * @return bool
     */
    public function can_show_tab()
    {
        return true;
    }
    /**
     * Return false always
     *
     * @return bool
     */
    public function is_hidden()
    {
        return false;
    }
    /**
     * Get parent tab code
     *
     * @return string
     */
    public function get_parent_tab()
    {
        return 'product-details';
    }
}