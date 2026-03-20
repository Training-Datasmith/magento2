<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\Controller\Result_Factory;
/**
 * Delete store view.
 */
class Delete_Store_Post extends \Magento\Backend\Controller\Adminhtml\System\Store implements Http_Post_Action_Interface
{
    /**
     * Delete store view post action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $item_id = $this->get_request()->get_param('item_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $redirect_result = $this->result_factory->create(Result_Factory::TYPE_REDIRECT);
        if (!$model = $this->_object_manager->create(\Magento\Store\Model\Store::class)->load($item_id)) {
            $this->message_manager->add_error_message(__('Something went wrong. Please try again.'));
            return $redirect_result->set_path('adminhtml/*/');
        }
        if (!$model->is_can_delete()) {
            $this->message_manager->add_error_message(__('This store view cannot be deleted.'));
            return $redirect_result->set_path('adminhtml/*/editStore', ['store_id' => $model->get_id()]);
        }
        if (!$this->_backup_database()) {
            return $redirect_result->set_path('*/*/editStore', ['store_id' => $item_id]);
        }
        try {
            $model->delete();
            $this->message_manager->add_success_message(__('You deleted the store view.'));
            return $redirect_result->set_path('adminhtml/*/');
        } catch (\Magento\Framework\Exception\Localized_Exception $e) {
            $this->message_manager->add_error_message($e->get_message());
        } catch (\Exception $e) {
            $this->message_manager->add_exception_message($e, __('Unable to delete the store view. Please try again later.'));
        }
        return $redirect_result->set_path('adminhtml/*/editStore', ['store_id' => $item_id]);
    }
}