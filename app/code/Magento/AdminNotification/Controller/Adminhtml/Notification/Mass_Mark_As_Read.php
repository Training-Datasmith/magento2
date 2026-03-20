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
use Magento\Framework\App\Action\Http_Post_Action_Interface;
/**
 * AdminNotification MassMarkAsRead controller
 */
class Mass_Mark_As_Read extends Notification implements Http_Post_Action_Interface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_AdminNotification::mark_as_read';
    public function __construct(Action\Context $context, private readonly Inbox_Model_Factory $inbox_model_factory)
    {
        parent::__construct($context);
    }
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $ids = $this->get_request()->get_param('notification');
        if (!is_array($ids)) {
            $this->message_manager->add_error_message(__('Please select messages.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = $this->inbox_model_factory->create()->load($id);
                    if ($model->get_id()) {
                        $model->set_is_read(1)->save();
                    }
                }
                $this->message_manager->add_success_message(__('A total of %1 record(s) have been marked as Read.', count($ids)));
            } catch (\Magento\Framework\Exception\Localized_Exception $e) {
                $this->message_manager->add_error_message($e->get_message());
            } catch (\Exception $e) {
                $this->message_manager->add_exception_message($e, __("We couldn't mark the notification as Read because of an error."));
            }
        }
        return $this->_redirect('adminhtml/*/');
    }
}