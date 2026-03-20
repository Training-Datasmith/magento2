<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Admin_Notification\Controller\Adminhtml\Notification;

use Magento\Admin_Notification\Controller\Adminhtml\Notification;
use Magento\Admin_Notification\Model\Notification_Service;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\Controller\Result_Factory;
/**
 * AdminNotification AjaxMarkAsRead controller
 */
class Ajax_Mark_As_Read extends Notification implements Http_Post_Action_Interface
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
     * Mark notification as read (AJAX action)
     *
     * @return \Magento\Framework\Controller\Result\Json|void
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        if (!$this->get_request()->get_post_value()) {
            return;
        }
        $notification_id = (int) $this->get_request()->get_post('id');
        $response_data = [];
        try {
            $this->notification_service->mark_as_read($notification_id);
            $response_data['success'] = true;
        } catch (\Exception) {
            $response_data['success'] = false;
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $result_json = $this->result_factory->create(Result_Factory::TYPE_JSON);
        $result_json->set_data($response_data);
        return $result_json;
    }
}