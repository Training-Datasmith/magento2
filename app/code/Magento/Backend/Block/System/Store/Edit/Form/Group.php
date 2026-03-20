<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Edit\Form;

/**
 * Adminhtml store edit form for group
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Group extends \Magento\Backend\Block\System\Store\Edit\Abstract_Form
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\Category
     */
    protected $_category;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_store_factory;
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_website_factory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\Config\Source\Category $category
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\Form_Factory $form_factory, \Magento\Catalog\Model\Config\Source\Category $category, \Magento\Store\Model\Store_Factory $store_factory, \Magento\Store\Model\Website_Factory $website_factory, array $data = [])
    {
        $this->_category = $category;
        $this->_store_factory = $store_factory;
        $this->_website_factory = $website_factory;
        parent::__construct($context, $registry, $form_factory, $data);
    }
    /**
     * Prepare group specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepare_store_fieldset(\Magento\Framework\Data\Form $form)
    {
        $group_model = $this->_core_registry->registry('store_data');
        $post_data = $this->_core_registry->registry('store_post_data');
        if ($post_data) {
            $group_model->set_data($post_data['group']);
        }
        $fieldset = $form->add_fieldset('group_fieldset', ['legend' => __('Store Information')]);
        $store_action = $this->_core_registry->registry('store_action');
        if ($store_action == 'edit' || $store_action == 'add') {
            $websites = $this->_website_factory->create()->get_collection()->to_option_array();
            $fieldset->add_field('group_website_id', 'select', ['name' => 'group[website_id]', 'label' => __('Web Site'), 'value' => $group_model->get_website_id(), 'values' => $websites, 'required' => true, 'disabled' => $group_model->is_read_only()]);
            if ($group_model->get_id() && $group_model->get_website()->get_default_group_id() == $group_model->get_id()) {
                if ($group_model->get_website()->get_is_default() || $group_model->get_website()->get_groups_count() == 1) {
                    $form->get_element('group_website_id')->set_disabled(true);
                    $fieldset->add_field('group_hidden_website_id', 'hidden', ['name' => 'group[website_id]', 'no_span' => true, 'value' => $group_model->get_website_id()]);
                } else {
                    $fieldset->add_field('group_original_website_id', 'hidden', ['name' => 'group[original_website_id]', 'no_span' => true, 'value' => $group_model->get_website_id()]);
                }
            }
        }
        $fieldset->add_field('group_name', 'text', ['name' => 'group[name]', 'label' => __('Name'), 'value' => $group_model->get_name(), 'required' => true, 'disabled' => $group_model->is_read_only()]);
        $fieldset->add_field('group_code', 'text', ['name' => 'group[code]', 'label' => __('Code'), 'value' => $group_model->get_code(), 'required' => true, 'disabled' => $group_model->is_read_only()]);
        $categories = $this->_category->to_option_array();
        $fieldset->add_field('group_root_category_id', 'select', ['name' => 'group[root_category_id]', 'label' => __('Root Category'), 'value' => $group_model->get_root_category_id(), 'values' => $categories, 'required' => true, 'disabled' => $group_model->is_read_only()]);
        if ($this->_core_registry->registry('store_action') == 'edit') {
            $store_active = 1;
            $stores = $this->_store_factory->create()->get_collection()->add_group_filter($group_model->get_id())->add_status_filter($store_active)->to_option_array();
            $fieldset->add_field('group_default_store_id', 'select', ['name' => 'group[default_store_id]', 'label' => __('Default Store View'), 'value' => $group_model->get_default_store_id(), 'values' => $stores, 'required' => false, 'disabled' => $group_model->is_read_only()]);
        }
        $fieldset->add_field('group_group_id', 'hidden', ['name' => 'group[group_id]', 'no_span' => true, 'value' => $group_model->get_id()]);
    }
}