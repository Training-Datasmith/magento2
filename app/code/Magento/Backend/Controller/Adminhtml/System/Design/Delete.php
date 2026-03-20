<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

use Magento\Framework\App\Action\Http_Post_Action_Interface;
/**
 * Delete store design schedule action.
 */
class Delete extends \Magento\Backend\Controller\Adminhtml\System\Design implements Http_Post_Action_Interface
{
    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->get_request()->get_param('id');
        if ($id) {
            $design = $this->_object_manager->create(\Magento\Framework\App\Design_Interface::class)->load($id);
            try {
                $design->delete();
                $this->message_manager->add_success_message(__('You deleted the design change.'));
            } catch (\Magento\Framework\Exception\Localized_Exception $e) {
                $this->message_manager->add_error_message($e->get_message());
            } catch (\Exception $e) {
                $this->message_manager->add_exception_message($e, __("You can't delete the design change."));
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_redirect_factory->create();
        return $result_redirect->set_path('adminhtml/*/');
    }
}