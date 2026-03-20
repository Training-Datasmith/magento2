<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\System\Store\Edit\Form;

/**
 * Adminhtml store edit form for website
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Website extends \Magento\Backend\Block\System\Store\Edit\Abstract_Form
{
    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_group_factory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, \Magento\Store\Model\Group_Factory $group_factory, array $data = [])
    {
        $this->_group_factory = $group_factory;
        parent::__construct($context, $registry, $form_factory, $data);
    }
    /**
     * Prepare website specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     */
    protected function _prepare_store_fieldset(\Magento\Framework\Data\Form $form)
    {
        $website_model = $this->_core_registry->registry('store_data');
        $post_data = $this->_core_registry->registry('store_post_data');
        if ($post_data) {
            $website_model->set_data($post_data['website']);
        }
        $fieldset = $form->add_fieldset('website_fieldset', ['legend' => __('Web Site Information')]);
        /* @var $fieldset \Magento\Framework\Data\Form */
        $fieldset->add_field('website_name', 'text', ['name' => 'website[name]', 'label' => __('Name'), 'value' => $website_model->get_name(), 'required' => true, 'disabled' => $website_model->is_read_only()]);
        $fieldset->add_field('website_code', 'text', ['name' => 'website[code]', 'label' => __('Code'), 'value' => $website_model->get_code(), 'required' => true, 'disabled' => $website_model->is_read_only()]);
        $fieldset->add_field('website_sort_order', 'text', ['name' => 'website[sort_order]', 'label' => __('Sort Order'), 'value' => $website_model->get_sort_order(), 'required' => false, 'class' => 'validate-number validate-zero-or-greater', 'disabled' => $website_model->is_read_only()]);
        if ($this->_core_registry->registry('store_action') == 'edit') {
            $groups = $this->_group_factory->create()->get_collection()->add_website_filter($website_model->get_id())->to_option_array();
            $fieldset->add_field('website_default_group_id', 'select', ['name' => 'website[default_group_id]', 'label' => __('Default Store'), 'value' => $website_model->get_default_group_id(), 'values' => $groups, 'required' => false, 'disabled' => $website_model->is_read_only()]);
        }
        $has_only_default_store = $website_model->get_stores_count() == 1 && array_key_exists(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $website_model->get_store_ids());
        if (!$website_model->get_is_default() && $website_model->get_stores_count() && !$has_only_default_store) {
            $fieldset->add_field('is_default', 'checkbox', ['name' => 'website[is_default]', 'label' => __('Set as Default'), 'value' => 1, 'disabled' => $website_model->is_read_only()]);
        } else {
            $fieldset->add_field('is_default', 'hidden', ['name' => 'website[is_default]', 'value' => $website_model->get_is_default()]);
        }
        $fieldset->add_field('website_website_id', 'hidden', ['name' => 'website[website_id]', 'value' => $website_model->get_id()]);
    }
}