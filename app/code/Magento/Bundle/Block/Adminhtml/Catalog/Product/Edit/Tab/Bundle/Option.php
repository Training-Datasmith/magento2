<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Data\Form\Element\Abstract_Element;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Store\Model\Store;
/**
 * Block for rendering option of bundle product
 *
 * Class \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option
 */
class Option extends \Magento\Backend\Block\Widget
{
    /**
     * Form element
     *
     * @var AbstractElement|null
     */
    protected $_element = null;
    /**
     * List of bundle product options
     *
     * @var array|null
     */
    protected $_options = null;
    /**
     * @var string
     */
    protected $_template = 'Magento_Bundle::product/edit/bundle/option.phtml';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry = null;
    /**
     * @var \Magento\Bundle\Model\Source\Option\Type
     */
    protected $_option_types;
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magento\Bundle\Model\Source\Option\Type $optionTypes
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Config\Model\Config\Source\Yesno $yesno, \Magento\Bundle\Model\Source\Option\Type $option_types, \Magento\Framework\Registry $registry, array $data = [], ?Json_Helper $json_helper = null)
    {
        $this->_core_registry = $registry;
        $this->_option_types = $option_types;
        $this->_yesno = $yesno;
        $data['jsonHelper'] = $json_helper ?? Object_Manager::get_instance()->get(Json_Helper::class);
        parent::__construct($context, $data);
    }
    /**
     * Bundle option renderer class constructor
     *
     * Sets block template and necessary data
     *
     * @return void
     */
    protected function _construct()
    {
        $this->set_can_read_price(true);
        $this->set_can_edit_price(true);
    }
    /**
     * Return Field Id
     *
     * @return string
     */
    public function get_field_id()
    {
        return 'bundle_option';
    }
    /**
     * Return Field Name
     *
     * @return string
     */
    public function get_field_name()
    {
        return 'bundle_options';
    }
    /**
     * Retrieve Product object
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
    /**
     * Render
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(Abstract_Element $element)
    {
        $this->set_element($element);
        return $this->to_html();
    }
    /**
     * Set Element
     *
     * @param AbstractElement $element
     * @return $this
     */
    public function set_element(Abstract_Element $element)
    {
        $this->_element = $element;
        return $this;
    }
    /**
     * Get Element
     *
     * @return AbstractElement|null
     */
    public function get_element()
    {
        return $this->_element;
    }
    /**
     * Return Is Multi Websites
     *
     * @return bool
     */
    public function is_multi_websites()
    {
        return !$this->_store_manager->has_single_store();
    }
    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->add_child('add_selection_button', \Magento\Backend\Block\Widget\Button::class, ['id' => $this->get_field_id() . '_<%- data.index %>_add_button', 'label' => __('Add Products to Option'), 'class' => 'add add-selection']);
        $this->add_child('close_search_button', \Magento\Backend\Block\Widget\Button::class, ['id' => $this->get_field_id() . '_<%- data.index %>_close_button', 'label' => __('Close'), 'on_click' => 'bSelection.closeSearch(event)', 'class' => 'back no-display']);
        $this->add_child('option_delete_button', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Delete Option'), 'class' => 'action-delete', 'on_click' => 'bOption.remove(event)']);
        $this->add_child('selection_template', \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Selection::class);
        return parent::_prepare_layout();
    }
    /**
     * Get Add Button Html
     *
     * @return string
     */
    public function get_add_button_html()
    {
        return $this->get_child_html('add_button');
    }
    /**
     * Get Close Search Button Html
     *
     * @return string
     */
    public function get_close_search_button_html()
    {
        return $this->get_child_html('close_search_button');
    }
    /**
     * Get Add Selection Button Html
     *
     * @return string
     */
    public function get_add_selection_button_html()
    {
        return $this->get_child_html('add_selection_button');
    }
    /**
     * Retrieve list of bundle product options
     *
     * @return array
     */
    public function get_options()
    {
        if (!$this->_options) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionCollection */
            $option_collection = $this->get_product()->get_type_instance()->get_options_collection($this->get_product());
            $selection_collection = $this->get_product()->get_type_instance()->get_selections_collection($this->get_product()->get_type_instance()->get_options_ids($this->get_product()), $this->get_product());
            $this->_options = $option_collection->append_selections($selection_collection);
            if ($this->get_can_read_price() === false) {
                foreach ($this->_options as $option) {
                    if ($option->get_selections()) {
                        foreach ($option->get_selections() as $selection) {
                            $selection->set_can_read_price($this->get_can_read_price());
                            $selection->set_can_edit_price($this->get_can_edit_price());
                        }
                    }
                }
            }
        }
        return $this->_options;
    }
    /**
     * Get Add Button Id
     *
     * @return mixed
     */
    public function get_add_button_id()
    {
        $button_id = $this->get_layout()->get_block('admin.product.bundle.items')->get_child_block('add_button')->get_id();
        return $button_id;
    }
    /**
     * Get Options Delete Button Html
     *
     * @return string
     */
    public function get_option_delete_button_html()
    {
        return $this->get_child_html('option_delete_button');
    }
    /**
     * Get Selection Html
     *
     * @return string
     */
    public function get_selection_html()
    {
        return $this->get_child_html('selection_template');
    }
    /**
     * Get Type Select Html
     *
     * @return mixed
     */
    public function get_type_select_html()
    {
        $select = $this->get_layout()->create_block(\Magento\Framework\View\Element\Html\Select::class)->set_data(['id' => $this->get_field_id() . '_<%- data.index %>_type', 'class' => 'select select-product-option-type required-option-select', 'extra_params' => 'onchange="bOption.changeType(event)"'])->set_name($this->get_field_name() . '[<%- data.index %>][type]')->set_options($this->_option_types->to_option_array());
        return $select->get_html();
    }
    /**
     * Get Require Select Html
     *
     * @return mixed
     */
    public function get_require_select_html()
    {
        $select = $this->get_layout()->create_block(\Magento\Framework\View\Element\Html\Select::class)->set_data(['id' => $this->get_field_id() . '_<%- data.index %>_required', 'class' => 'select'])->set_name($this->get_field_name() . '[<%- data.index %>][required]')->set_options($this->_yesno->to_option_array());
        return $select->get_html();
    }
    /**
     * Return Is Default Store
     *
     * @return bool
     */
    public function is_default_store()
    {
        return $this->get_product()->get_store_id() == Store::DEFAULT_STORE_ID;
    }
}