<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option;

/**
 * Bundle selection renderer
 */
class Selection extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Bundle::product/edit/bundle/option/selection.phtml';
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalog_data = null;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry = null;
    /**
     * @var \Magento\Bundle\Model\Source\Option\Selection\Price\Type
     */
    protected $_price_type;
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magento\Bundle\Model\Source\Option\Selection\Price\Type $priceType
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Config\Model\Config\Source\Yesno $yesno, \Magento\Bundle\Model\Source\Option\Selection\Price\Type $price_type, \Magento\Catalog\Helper\Data $catalog_data, \Magento\Framework\Registry $registry, array $data = [])
    {
        $this->_catalog_data = $catalog_data;
        $this->_core_registry = $registry;
        $this->_price_type = $price_type;
        $this->_yesno = $yesno;
        parent::__construct($context, $data);
    }
    /**
     * Initialize bundle option selection block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->set_can_read_price(true);
        $this->set_can_edit_price(true);
    }
    /**
     * Return field id
     *
     * @return string
     */
    public function get_field_id()
    {
        return 'bundle_selection';
    }
    /**
     * Return field name
     *
     * @return string
     */
    public function get_field_name()
    {
        return 'bundle_selections';
    }
    /**
     * Prepare block layout
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->add_child('selection_delete_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Delete'), 'class' => 'action-delete', 'on_click' => 'bSelection.remove(event)']);
        return parent::_prepare_layout();
    }
    /**
     * Retrieve delete button html
     *
     * @return string
     */
    public function get_selection_delete_button_html()
    {
        return $this->get_child_html('selection_delete_button');
    }
    /**
     * Retrieve price type select html
     *
     * @return string
     */
    public function get_price_type_select_html()
    {
        $select = $this->get_layout()->create_block(\Magento\Framework\View\Element\Html\Select::class)->set_data(['id' => $this->get_field_id() . '_<%- data.index %>_price_type', 'class' => 'select select-product-option-type required-option-select'])->set_name($this->get_field_name() . '[<%- data.parentIndex %>][<%- data.index %>][selection_price_type]')->set_options($this->_price_type->to_option_array());
        if ($this->get_can_edit_price() === false) {
            $select->set_extra_params('disabled="disabled"');
        }
        return $select->get_html();
    }
    /**
     * Retrieve qty type select html
     *
     * @return string
     */
    public function get_qty_type_select_html()
    {
        $select = $this->get_layout()->create_block(\Magento\Framework\View\Element\Html\Select::class)->set_data(['id' => $this->get_field_id() . '_<%- data.index %>_can_change_qty', 'class' => 'select'])->set_name($this->get_field_name() . '[<%- data.parentIndex %>][<%- data.index %>][selection_can_change_qty]')->set_options($this->_yesno->to_option_array());
        return $select->get_html();
    }
    /**
     * Return search url
     *
     * @return string
     */
    public function get_selection_search_url()
    {
        return $this->get_url('adminhtml/bundle_selection/grid');
    }
    /**
     * Check if used website scope price
     *
     * @return string
     */
    public function is_used_website_price()
    {
        $product = $this->_core_registry->registry('product');
        return !$this->_catalog_data->is_price_global() && $product->get_store_id();
    }
    /**
     * Retrieve price scope checkbox html
     *
     * @return string
     */
    public function get_checkbox_scope_html()
    {
        $checkbox_html = '';
        if ($this->is_used_website_price()) {
            $fields_id = $this->get_field_id() . '_<%- data.index %>_price_scope';
            $name = $this->get_field_name() . '[<%- data.parentIndex %>][<%- data.index %>][default_price_scope]';
            $class = 'bundle-option-price-scope-checkbox';
            $label = __('Use Default Value');
            $disabled = $this->get_can_edit_price() === false ? ' disabled="disabled"' : '';
            $checkbox_html = '<input type="checkbox" id="' . $fields_id . '" class="' . $class . '" name="' . $name . '"' . $disabled . ' value="1" />';
            $checkbox_html .= '<label class="normal" for="' . $fields_id . '">' . $label . '</label>';
        }
        return $checkbox_html;
    }
}