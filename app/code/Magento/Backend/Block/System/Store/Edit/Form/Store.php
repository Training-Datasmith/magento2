<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\System\Store\Edit\Form;

/**
 * Adminhtml store edit form for store
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Store extends \Magento\Backend\Block\System\Store\Edit\Abstract_Form
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_website_factory;
    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_group_factory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, \Magento\Store\Model\Group_Factory $group_factory, \Magento\Store\Model\Website_Factory $website_factory, array $data = [])
    {
        $this->_group_factory = $group_factory;
        $this->_website_factory = $website_factory;
        parent::__construct($context, $registry, $form_factory, $data);
    }
    /**
     * Prepare store specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     */
    protected function _prepare_store_fieldset(\Magento\Framework\Data\Form $form)
    {
        $store_model = $this->_core_registry->registry('store_data');
        $post_data = $this->_core_registry->registry('store_post_data');
        if ($post_data) {
            $store_model->set_data($post_data['store']);
        }
        $fieldset = $form->add_fieldset('store_fieldset', ['legend' => __('Store View Information')]);
        $store_action = $this->_core_registry->registry('store_action');
        if ($store_action == 'edit' || $store_action == 'add') {
            $fieldset->add_field('store_group_id', 'select', ['name' => 'store[group_id]', 'label' => __('Store'), 'value' => $store_model->get_group_id(), 'values' => $this->_get_store_groups(), 'required' => true, 'disabled' => $store_model->is_read_only()]);
            $fieldset = $this->prepare_group_id_field($form, $store_model, $fieldset);
        }
        $fieldset->add_field('store_name', 'text', ['name' => 'store[name]', 'label' => __('Name'), 'value' => $store_model->get_name(), 'required' => true, 'disabled' => $store_model->is_read_only()]);
        $fieldset->add_field('store_code', 'text', ['name' => 'store[code]', 'label' => __('Code'), 'value' => $store_model->get_code(), 'required' => true, 'disabled' => $store_model->is_read_only()]);
        $is_disabled_status_field = $store_model->is_read_only() || $store_model->get_id() && $store_model->is_default() && $store_model->is_active();
        $fieldset->add_field('store_is_active', 'select', ['name' => 'store[is_active]', 'label' => __('Status'), 'value' => $store_model->is_active(), 'options' => [0 => __('Disabled'), 1 => __('Enabled')], 'required' => true, 'disabled' => $is_disabled_status_field]);
        if ($is_disabled_status_field) {
            $fieldset->add_field('store_is_active_hidden', 'hidden', ['name' => 'store[is_active]', 'value' => $store_model->is_active()]);
        }
        $fieldset->add_field('store_sort_order', 'text', ['name' => 'store[sort_order]', 'label' => __('Sort Order'), 'value' => $store_model->get_sort_order(), 'required' => false, 'class' => 'validate-number validate-zero-or-greater', 'disabled' => $store_model->is_read_only()]);
        $fieldset->add_field('store_is_default', 'hidden', ['name' => 'store[is_default]', 'no_span' => true, 'value' => $store_model->get_is_default()]);
        $fieldset->add_field('store_store_id', 'hidden', ['name' => 'store[store_id]', 'no_span' => true, 'value' => $store_model->get_id(), 'disabled' => $store_model->is_read_only()]);
    }
    /**
     * Retrieve list of store groups
     *
     * @return array
     */
    protected function _get_store_groups()
    {
        $websites = $this->_website_factory->create()->get_collection();
        $allgroups = $this->_group_factory->create()->get_collection();
        $groups = [];
        foreach ($websites as $website) {
            $values = [];
            foreach ($allgroups as $group) {
                if ($group->get_website_id() == $website->get_id()) {
                    $values[] = ['label' => $group->get_name(), 'value' => $group->get_id()];
                }
            }
            $groups[] = ['label' => $website->get_name(), 'value' => $values];
        }
        return $groups;
    }
    /**
     * Prepare group id field in the fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Magento\Store\Model\Store $storeModel
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    private function prepare_group_id_field(\Magento\Framework\Data\Form $form, \Magento\Store\Model\Store $store_model, \Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        if ($store_model->get_id() && $store_model->get_group()->get_default_store_id() == $store_model->get_id()) {
            if ($store_model->get_group() && $store_model->get_group()->get_stores_count() > 1) {
                $form->get_element('store_group_id')->set_disabled(true);
                $fieldset->add_field('store_hidden_group_id', 'hidden', ['name' => 'store[group_id]', 'no_span' => true, 'value' => $store_model->get_group_id()]);
            } else {
                $fieldset->add_field('store_original_group_id', 'hidden', ['name' => 'store[original_group_id]', 'no_span' => true, 'value' => $store_model->get_group_id()]);
            }
        }
        return $fieldset;
    }
}