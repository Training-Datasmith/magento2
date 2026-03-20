<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

class Delete_Store extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $item_id = $this->get_request()->get_param('item_id', null);
        if (!$model = $this->_object_manager->create(\Magento\Store\Model\Store::class)->load($item_id)) {
            $this->message_manager->add_error_message(__('Something went wrong. Please try again.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirect_result = $this->result_redirect_factory->create();
            return $redirect_result->set_path('adminhtml/*/');
        }
        if (!$model->is_can_delete()) {
            $this->message_manager->add_error_message(__('This store view cannot be deleted.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirect_result = $this->result_redirect_factory->create();
            return $redirect_result->set_path('adminhtml/*/editStore', ['store_id' => $item_id]);
        }
        $this->_add_deletion_notice('store view');
        $result_page = $this->create_page();
        $result_page->get_config()->get_title()->prepend(__('Delete Store View'));
        $result_page->add_breadcrumb(__('Delete Store View'), __('Delete Store View'))->add_content($result_page->get_layout()->create_block(\Magento\Backend\Block\System\Store\Delete::class)->set_form_action_url($this->get_url('adminhtml/*/deleteStorePost'))->set_back_url($this->get_url('adminhtml/*/editStore', ['store_id' => $item_id]))->set_store_type_title(__('Store View'))->set_data_object($model));
        return $result_page;
    }
}