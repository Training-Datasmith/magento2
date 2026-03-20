<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
class Delete_Website extends \Magento\Backend\Controller\Adminhtml\System\Store implements Http_Get_Action_Interface
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $item_id = $this->get_request()->get_param('item_id', null);
        if (!$model = $this->_object_manager->create(\Magento\Store\Model\Website::class)->load($item_id)) {
            $this->message_manager->add_error_message(__('Something went wrong. Please try again.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirect_result = $this->result_redirect_factory->create();
            return $redirect_result->set_path('adminhtml/*/');
        }
        if (!$model->is_can_delete()) {
            $this->message_manager->add_error_message(__('This website cannot be deleted.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirect_result = $this->result_redirect_factory->create();
            return $redirect_result->set_path('adminhtml/*/editWebsite', ['website_id' => $item_id]);
        }
        $this->_add_deletion_notice('website');
        $result_page = $this->create_page();
        $result_page->get_config()->get_title()->prepend(__('Delete Web Site'));
        $result_page->add_breadcrumb(__('Delete Web Site'), __('Delete Web Site'))->add_content($result_page->get_layout()->create_block(\Magento\Backend\Block\System\Store\Delete::class)->set_form_action_url($this->get_url('adminhtml/*/deleteWebsitePost'))->set_back_url($this->get_url('adminhtml/*/editWebsite', ['website_id' => $item_id]))->set_store_type_title(__('Web Site'))->set_data_object($model));
        return $result_page;
    }
}