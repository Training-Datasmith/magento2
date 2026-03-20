<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Framework\App\Action\Http_Post_Action_Interface as HttpPostActionInterface;
use Magento\Framework\Controller\Result_Factory;
/**
 * Delete website.
 */
class Delete_Website_Post extends \Magento\Backend\Controller\Adminhtml\System\Store implements Http_Post_Action_Interface
{
    /**
     * @inheritDoc
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $item_id = $this->get_request()->get_param('item_id');
        $model = $this->_object_manager->create(\Magento\Store\Model\Website::class);
        $model->load($item_id);
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $redirect_result = $this->result_factory->create(Result_Factory::TYPE_REDIRECT);
        if (!$model) {
            $this->message_manager->add_error_message(__('Something went wrong. Please try again.'));
            return $redirect_result->set_path('adminhtml/*/');
        }
        if (!$model->is_can_delete()) {
            $this->message_manager->add_error_message(__('This website cannot be deleted.'));
            return $redirect_result->set_path('adminhtml/*/editWebsite', ['website_id' => $model->get_id()]);
        }
        if (!$this->_backup_database()) {
            return $redirect_result->set_path('*/*/editWebsite', ['website_id' => $item_id]);
        }
        try {
            $model->delete();
            $this->message_manager->add_success_message(__('You deleted the website.'));
            return $redirect_result->set_path('adminhtml/*/');
        } catch (\Magento\Framework\Exception\Localized_Exception $e) {
            $this->message_manager->add_error_message($e->get_message());
        } catch (\Exception $e) {
            $this->message_manager->add_exception_message($e, __('Unable to delete the website. Please try again later.'));
        }
        return $redirect_result->set_path('*/*/editWebsite', ['website_id' => $item_id]);
    }
}