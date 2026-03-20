<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Controller\Adminhtml\Notification;

use Magento\Admin_Notification\Controller\Adminhtml\Notification;
use Magento\Admin_Notification\Model\Notification_Service;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\Http_Get_Action_Interface;
use Magento\Framework\Exception\Localized_Exception;
/**
 * AdminNotification MarkAsRead controller
 */
class Mark_As_Read extends Notification implements Http_Get_Action_Interface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_AdminNotification::mark_as_read';
    public function __construct(Action\Context $context, private readonly Notification_Service $notification_service)
    {
        parent::__construct($context);
    }
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $notification_id = (int) $this->get_request()->get_param('id');
        if ($notification_id) {
            try {
                $this->notification_service->mark_as_read($notification_id);
                $this->message_manager->add_success_message(__('The message has been marked as Read.'));
            } catch (Localized_Exception $e) {
                $this->message_manager->add_error_message($e->get_message());
            } catch (\Exception $e) {
                $this->message_manager->add_exception_message($e, __("We couldn't mark the notification as Read because of an error."));
            }
            return $this->get_response()->set_redirect($this->_redirect->get_redirect_url($this->get_url('*')));
        }
        return $this->_redirect('adminhtml/*/');
    }
}