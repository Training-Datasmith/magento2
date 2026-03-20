<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Controller\Adminhtml\Notification;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
class Index extends \Magento\Admin_Notification\Controller\Adminhtml\Notification implements Http_Get_Action_Interface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $result_page = $this->result_factory->create(\Magento\Framework\Controller\Result_Factory::TYPE_PAGE);
        $result_page->set_active_menu('Magento_AdminNotification::system_adminnotification');
        $result_page->add_breadcrumb(__('Messages Inbox'), __('Messages Inbox'));
        $result_page->get_config()->get_title()->prepend(__('Notifications'));
        return $result_page;
    }
}