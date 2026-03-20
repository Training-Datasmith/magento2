<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\Filter\Filter_Input;
/**
 * Save design action.
 */
class Save extends \Magento\Backend\Controller\Adminhtml\System\Design implements Http_Post_Action_Interface
{
    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array|null
     */
    protected function _filter_post_data($data)
    {
        $input_filter = new Filter_Input(['date_from' => $this->date_filter, 'date_to' => $this->date_filter], [], $data);
        return $input_filter->get_unescaped();
    }
    /**
     * Save design action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->get_request()->get_post_value();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_redirect_factory->create();
        if ($data) {
            $data['design'] = $this->_filter_post_data($data['design']);
            $id = (int) $this->get_request()->get_param('id');
            $design = $this->_object_manager->create(\Magento\Framework\App\Design_Interface::class);
            if ($id) {
                $design->load($id);
            }
            $design->set_data($data['design']);
            if ($id) {
                $design->set_id($id);
            }
            try {
                $design->save();
                $this->_event_manager->dispatch('theme_save_after');
                $this->message_manager->add_success_message(__('You saved the design change.'));
            } catch (\Exception $e) {
                $this->message_manager->add_error_message($e->get_message());
                $this->_object_manager->get(\Magento\Backend\Model\Session::class)->set_design_data($data);
                return $result_redirect->set_path('*/*/edit', ['id' => $design->get_id()]);
            }
        }
        return $result_redirect->set_path('*/*/');
    }
}