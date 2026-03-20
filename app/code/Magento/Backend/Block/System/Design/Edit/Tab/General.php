<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Design\Edit\Tab;

/**
 * General system tab block.
 */
class General extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_label_factory;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_system_store;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, \Magento\Framework\View\Design\Theme\Label_Factory $label_factory, \Magento\Store\Model\System\Store $system_store, array $data = [])
    {
        $this->_label_factory = $label_factory;
        $this->_system_store = $system_store;
        parent::__construct($context, $registry, $form_factory, $data);
    }
    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _prepare_form()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_form_factory->create();
        $fieldset = $form->add_fieldset('general', ['legend' => __('General Settings')]);
        if (!$this->_store_manager->is_single_store_mode()) {
            $field = $fieldset->add_field('store_id', 'select', ['label' => __('Store'), 'title' => __('Store'), 'values' => $this->_system_store->get_store_values_for_form(), 'name' => 'store_id', 'required' => true]);
            $renderer = $this->get_layout()->create_block(\Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class);
            $field->set_renderer($renderer);
        } else {
            $fieldset->add_field('store_id', 'hidden', ['name' => 'store_id', 'value' => $this->_store_manager->get_store(true)->get_id()]);
        }
        /** @var $label \Magento\Framework\View\Design\Theme\Label */
        $label = $this->_label_factory->create();
        $options = $label->get_labels_collection(__('-- Please Select --'));
        $fieldset->add_field('design', 'select', ['label' => __('Custom Design'), 'title' => __('Custom Design'), 'values' => $options, 'name' => 'design', 'required' => true]);
        $date_format = $this->_locale_date->get_date_format_with_long_year();
        $fieldset->add_field('date_from', 'date', ['label' => __('Date From'), 'title' => __('Date From'), 'name' => 'date_from', 'date_format' => $date_format]);
        $fieldset->add_field('date_to', 'date', ['label' => __('Date To'), 'title' => __('Date To'), 'name' => 'date_to', 'date_format' => $date_format]);
        $form_data = $this->_backend_session->get_design_data(true);
        if (!$form_data) {
            $form_data = $this->_core_registry->registry('design')->get_data();
        } else {
            $form_data = $form_data['design'];
        }
        $form->add_values($form_data);
        $form->set_field_name_suffix('design');
        $this->set_form($form);
    }
}