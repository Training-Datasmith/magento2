<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
class Edit_Store extends \Magento\Backend\Controller\Adminhtml\System\Store implements Http_Get_Action_Interface
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if ($this->_get_session()->get_post_data()) {
            $this->_core_registry->register('store_post_data', $this->_get_session()->get_post_data());
            $this->_get_session()->uns_post_data();
        }
        if (!$this->_core_registry->registry('store_type')) {
            $this->_core_registry->register('store_type', 'store');
        }
        if (!$this->_core_registry->registry('store_action')) {
            $this->_core_registry->register('store_action', 'edit');
        }
        switch ($this->_core_registry->registry('store_type')) {
            case 'website':
                $item_id = $this->get_request()->get_param('website_id', null);
                $model = $this->_object_manager->create(\Magento\Store\Model\Website::class);
                $title = __('Web Site');
                $not_exists = __('The website does not exist.');
                $code_base = __('Before modifying the website code please make sure it is not used in index.php.');
                break;
            case 'group':
                $item_id = $this->get_request()->get_param('group_id', null);
                $model = $this->_object_manager->create(\Magento\Store\Model\Group::class);
                $title = __('Store');
                $not_exists = __('The store does not exist');
                $code_base = false;
                break;
            case 'store':
                $item_id = $this->get_request()->get_param('store_id', null);
                $model = $this->_object_manager->create(\Magento\Store\Model\Store::class);
                $title = __('Store View');
                $not_exists = __("Store view doesn't exist");
                $code_base = __('Before modifying the store view code please make sure it is not used in index.php.');
                break;
            default:
                break;
        }
        if (null !== $item_id) {
            $model->load($item_id);
        }
        if ($model->get_id() || $this->_core_registry->registry('store_action') == 'add') {
            $this->_core_registry->register('store_data', $model);
            if ($this->_core_registry->registry('store_action') == 'edit' && $code_base && !$model->is_read_only()) {
                $this->message_manager->add_notice_message($code_base);
            }
            $result_page = $this->create_page();
            if ($this->_core_registry->registry('store_action') == 'add') {
                $result_page->get_config()->get_title()->prepend(__('New ') . $title);
            } else {
                $result_page->get_config()->get_title()->prepend($model->get_name());
            }
            $result_page->get_config()->get_title()->prepend(__('Stores'));
            $result_page->add_content($result_page->get_layout()->create_block(\Magento\Backend\Block\System\Store\Edit::class));
            return $result_page;
        } else {
            $this->message_manager->add_error_message($not_exists);
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $result_redirect = $this->result_redirect_factory->create();
            return $result_redirect->set_path('adminhtml/*/');
        }
    }
}