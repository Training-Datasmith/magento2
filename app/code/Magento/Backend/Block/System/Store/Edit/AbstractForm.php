<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Edit;

/**
 * Adminhtml store edit form
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
abstract class Abstract_Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id('coreStoreForm');
    }
    /**
     * Prepare form data
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepare_form()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_form_factory->create(['data' => ['id' => 'edit_form', 'action' => $this->get_data('action'), 'method' => 'post']]);
        $this->_prepare_store_fieldset($form);
        $form->add_field('store_type', 'hidden', ['name' => 'store_type', 'no_span' => true, 'value' => $this->_core_registry->registry('store_type')]);
        $form->add_field('store_action', 'hidden', ['name' => 'store_action', 'no_span' => true, 'value' => $this->_core_registry->registry('store_action')]);
        $form->set_action($this->get_url('adminhtml/*/save'));
        $form->set_use_container(true);
        $this->set_form($form);
        $this->_event_manager->dispatch('adminhtml_store_edit_form_prepare_form', ['block' => $this]);
        return parent::_prepare_form();
    }
    /**
     * Build store type specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     * @abstract
     */
    abstract protected function _prepare_store_fieldset(\Magento\Framework\Data\Form $form);
}