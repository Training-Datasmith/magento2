<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Controller\Adminhtml\Notification;

use Magento\Admin_Notification\Controller\Adminhtml\Notification;
use Magento\Admin_Notification\Model\Inbox_Factory as InboxModelFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\Http_Get_Action_Interface;
/**
 * AdminNotification Remove controller
 */
class Remove extends Notification implements Http_Get_Action_Interface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_AdminNotification::adminnotification_remove';
    public function __construct(Action\Context $context, private readonly Inbox_Model_Factory $inbox_model_factory)
    {
        parent::__construct($context);
    }
    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($id = $this->get_request()->get_param('id')) {
            $model = $this->inbox_model_factory->create()->load($id);
            if (!$model->get_id()) {
                return $this->_redirect('adminhtml/*/');
            }
            try {
                $model->set_is_remove(1)->save();
                $this->message_manager->add_success_message(__('The message has been removed.'));
            } catch (\Magento\Framework\Exception\Localized_Exception $e) {
                $this->message_manager->add_error_message($e->get_message());
            } catch (\Exception $e) {
                $this->message_manager->add_exception_message($e, __("We couldn't remove the messages because of an error."));
            }
            return $this->_redirect('adminhtml/*/');
        }
        return $this->_redirect('adminhtml/*/');
    }
}